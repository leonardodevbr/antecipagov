<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Contract
 * 
 * @property int $id
 * @property int $proposal_id
 * @property string $year_number
 * @property string $uasg
 * @property string $uasg_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Proposal $proposal
 *
 * @package App\Models
 */
class Contract extends Model
{
	protected $table = 'contracts';

	protected $casts = [
		'proposal_id' => 'int'
	];

	protected $fillable = [
		'proposal_id',
		'year_number',
		'uasg',
		'uasg_name'
	];

	public function proposal()
	{
		return $this->belongsTo(Proposal::class);
	}
}
