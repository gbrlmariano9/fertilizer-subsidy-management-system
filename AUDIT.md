# Technical Audit Report

Inilalarawan ng report na ito ang kasalukuyang Laravel implementation ng repository. Ang payment flow ay gumagamit pa ng local simulator para sa development at assessment; hindi pa ito isang live payment-gateway integration.

## Module 4: Cryptographic Security and Concurrency

- **Tamper-proof QR:** Ang `TransactionQrService` ay nagse-serialize ng QR fields at pumipirma sa mga ito gamit ang HMAC-SHA256 at Laravel application key. Sa checkout review endpoint, bine-verify ang signature gamit ang `hash_equals`, pagkatapos ay ikinukumpara ang beneficiary ID at two-decimal total sa transaction at farmer records sa database. Kaya kapag binago ang `beneficiary_id` o `total_amount`, hindi ito papasa sa signature verification at authoritative-data checks. Hiwalay ding bine-verify ng webhook endpoint ang raw request body gamit ang `PAYMENT_WEBHOOK_SECRET`.

- **TTL and Reference:** Nag-e-expire ang QR 15 minutes pagkatapos gawin ang order, gamit ang `qr_expires_at`. Ang signed reference ay nasa format na `BeneficiaryID|UUID|Timestamp`, at nire-reconstruct ang value na ito mula sa database records para sa validation. Tinatanggihan ang expired QR sa verification at display.

- **Pessimistic Concurrency:** Gumagamit ang order creation at settlement ng database transactions at `lockForUpdate()` para sa farmer, merchant, transaction, at mga apektadong produkto. Habang naka-lock ang records, chine-check ang available stock at balance bago i-update ang inventory, farmer balance, merchant wallet, at transaction status sa loob ng iisang database transaction. Nakakatulong ito na maiwasan ang conflicting updates mula sa sabay-sabay na checkout o settlement. Kapag nagkaroon ng failure, maaaring i-roll back ang magkakaugnay na changes. Para sa production, kailangang gumamit ng database engine na sumusuporta sa row-level locking, gaya ng MySQL/InnoDB.

- **Webhook Idempotency:** Chine-check ng settlement process kung existing na ang event ID sa `payment_logs`. Inuulit ang check pagkatapos ma-lock ang transaction, at nilo-log ang mga processed o ignored events. May unique constraint ang event identifier sa migration. Dahil dito, ang duplicate webhook retries ay dapat matukoy bilang `duplicate_event` at hindi dapat mag-apply ng balance o stock changes nang dalawang beses. Ginagamit din ng polling path ang parehong settlement service at mayroon itong stable event ID.

## Module 6: Architecture Viva

- **Privacy / Exact Beneficiary Lookup:** Gumagamit ang POS ng exact query na `where('beneficiary_id', submittedValue)` at ibinabalik lamang ang eligible at verified farmer. Ang search o autocomplete ay maaaring maglantad ng partial identifiers o pangalan, maghikayat ng enumeration, at magresulta sa maling beneficiary selection. Sa exact entry, nakatuon ang lookup sa isang claimed ID. Gayunpaman, server-side pa ring chine-check ang authorization at eligibility kapag ginagawa ang order.

- **Gateway Abstraction:** Ipinapasa ng controllers ang payment events sa `PaymentSettlementService`, na responsable sa validation ng order at payment values, pati na rin sa atomic business updates. Magkakahiwalay na components ang webhook authentication at local simulator, ngunit parehong ginagamit ng webhook at polling routes ang shared settlement process. Sa kasalukuyan, local simulator pa lamang ang provider implementation. Para sa totoong payment gateway, kailangang gumawa ng adapter na sumusunod sa checkout, status, at event protocol ng provider at nagta-translate ng verified provider events sa existing settlement contract.

- **Connectivity Tolerance / Dual Reconciliation:** Hindi itinuturing na patunay ng payment ang pagbabalik ng user sa redirect page. Ang server ay nagse-settle ng payment mula sa signed webhook, habang ang authenticated status-polling endpoint ay maaaring mag-reconcile ng paid result sa pamamagitan ng parehong idempotent service kung mawalan ng connectivity ang browser o ma-interrupt ang redirect. Ang shared transaction locks at event log ay tumutulong na pigilan ang dobleng settlement. Sa repository na ito, ang polling ay nagche-check pa lamang ng local simulator. Para sa production, kailangang may authoritative server-to-server status lookup ang totoong gateway upang maging maaasahan ang fallback.

## Audit Caveats

Ang initial payment-log migration ay gumagawa ng unique nullable `gateway_event_id`; sa isang later migration, pinapalitan ang pangalan nito ng `event_id` upang tumugma sa model at settlement code. Para sa bagong database, kailangang patakbuhin ang **lahat ng migrations sa tamang pagkakasunod-sunod** upang gumana ang idempotency lookup.

Ang local simulator ay nagpapakita ng intended architecture, ngunit hindi nito pinatutunayan na may live gateway connectivity o totoong money settlement. Panatilihing private ang `APP_KEY` at `PAYMENT_WEBHOOK_SECRET`, at tiyaking securely configured ang mga ito sa bawat environment.
