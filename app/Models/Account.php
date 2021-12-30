<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Account
 * 
 * @property int $id
 * @property string|null $document
 * @property string|null $name
 * @property string|null $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Proposal[] $proposals
 *
 * @package App\Models
 */
class Account extends Model
{
	protected $table = 'accounts';

	protected $fillable = [
		'document',
		'name',
		'email'
	];

	public function proposals()
	{
		return $this->hasMany(Proposal::class);
	}
}
