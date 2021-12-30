<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Proposal
 * 
 * @property int $id
 * @property int $account_id
 * @property int $product_id
 * @property string $code
 * @property string|null $external_proposal_id
 * @property float|null $amount
 * @property float|null $payment_flow
 * @property float|null $net_amount
 * @property string|null $status
 * @property int|null $quota_qty
 * @property float|null $quota_amount
 * @property float|null $fine_amount
 * @property float|null $insurance_amount
 * @property float|null $tax
 * @property float|null $late_tax
 * @property float|null $iof
 * @property float|null $tac
 * @property float|null $cet
 * @property Carbon|null $release_date
 * @property string|null $accepted
 * @property string|null $modality
 * @property Carbon|null $loaned_at
 * @property Carbon|null $last_quota_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Account $account
 * @property Product $product
 * @property Collection|Contract[] $contracts
 *
 * @package App\Models
 */
class Proposal extends Model
{
	protected $table = 'proposals';

	protected $casts = [
		'account_id' => 'int',
		'product_id' => 'int',
		'amount' => 'float',
		'payment_flow' => 'float',
		'net_amount' => 'float',
		'quota_qty' => 'int',
		'quota_amount' => 'float',
		'fine_amount' => 'float',
		'insurance_amount' => 'float',
		'tax' => 'float',
		'late_tax' => 'float',
		'iof' => 'float',
		'tac' => 'float',
		'cet' => 'float'
	];

	protected $dates = [
		'release_date',
		'loaned_at',
		'last_quota_at'
	];

	protected $fillable = [
		'account_id',
		'product_id',
		'code',
		'external_proposal_id',
		'amount',
		'payment_flow',
		'net_amount',
		'status',
		'quota_qty',
		'quota_amount',
		'fine_amount',
		'insurance_amount',
		'tax',
		'late_tax',
		'iof',
		'tac',
		'cet',
		'release_date',
		'accepted',
		'modality',
		'loaned_at',
		'last_quota_at'
	];

	public function account()
	{
		return $this->belongsTo(Account::class);
	}

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function contracts()
	{
		return $this->hasMany(Contract::class);
	}
}
