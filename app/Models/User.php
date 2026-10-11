<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\Withdrawal;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function products()
    {
        return $this->hasMany(Product::class, 'merchant_id');
    }

    public function farmerTransactions()
    {
        return $this->hasMany(Transaction::class, 'farmer_id');
    }

    public function merchantTransactions()
    {
        return $this->hasMany(Transaction::class, 'merchant_id');
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class, 'merchant_id');
    }

    public function isFarmer(): bool
    {
        return $this->role === 'farmer';
    }

    public function isMerchant(): bool
    {
        return $this->role === 'merchant';
    }
}
