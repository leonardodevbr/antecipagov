<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 * 
 * @property int $id
 * @property string|null $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Proposal[] $proposals
 *
 * @package App\Models
 */
class Product extends Model
{
	protected $table = 'products';

	protected $fillable = [
		'name'
	];

	public function proposals()
	{
		return $this->hasMany(Proposal::class);
	}
}
