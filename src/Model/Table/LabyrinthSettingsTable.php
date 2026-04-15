<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class LabyrinthSettingsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('labyrinth_settings');
        $this->setPrimaryKey('id');
        $this->belongsTo('Games');
    }
}
