<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class GamesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('games');
        $this->setPrimaryKey('id');

        $this->belongsTo('BoardGames');
        $this->hasMany('UsersIngames');
        $this->hasOne('MastermindSettings');
        $this->hasOne('FillerSettings');
        $this->hasOne('LabyrinthSettings');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('board_game_id')
            ->requirePresence('board_game_id', 'create')
            ->notEmptyString('board_game_id');

        $validator
            ->scalar('status')
            ->requirePresence('status', 'create')
            ->notEmptyString('status');

        return $validator;
    }
}
