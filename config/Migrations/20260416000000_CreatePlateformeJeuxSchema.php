<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreatePlateformeJeuxSchema extends BaseMigration
{
    public function up(): void
    {
        $this->table('board_games')
            ->addColumn('name', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('type', 'enum', [
                'values' => ['solo', 'multiplayer'],
                'null' => false,
            ])
            ->create();

        $this->table('users')
            ->addColumn('username', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('email', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => true,
                'update' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['username'], ['unique' => true])
            ->addIndex(['email'], ['unique' => true])
            ->create();

        $this->table('games')
            ->addColumn('board_game_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('status', 'enum', [
                'values' => ['waiting', 'in_progress', 'finished'],
                'default' => 'waiting',
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => true,
                'update' => 'CURRENT_TIMESTAMP',
            ])
            ->addForeignKey('board_game_id', 'board_games', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'NO_ACTION',
            ])
            ->create();

        $this->table('users_ingames')
            ->addColumn('user_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('game_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('nom', 'string', [
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('score_final', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('game_id', 'games', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->create();

        $this->table('mastermind_settings')
            ->addColumn('game_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('combinaison', 'string', [
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('steps', 'text', [
                'null' => true,
            ])
            ->addIndex(['game_id'], ['unique' => true])
            ->addForeignKey('game_id', 'games', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->create();

        $this->table('filler_settings')
            ->addColumn('game_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('grid', 'text', [
                'null' => false,
            ])
            ->addColumn('current_player', 'integer', [
                'default' => 1,
                'null' => false,
            ])
            ->addIndex(['game_id'], ['unique' => true])
            ->addForeignKey('game_id', 'games', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->create();

        $this->table('labyrinth_settings')
            ->addColumn('game_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('map', 'text', [
                'null' => false,
            ])
            ->addColumn('treasure_x', 'integer', [
                'null' => false,
            ])
            ->addColumn('treasure_y', 'integer', [
                'null' => false,
            ])
            ->addColumn('pos_p1_x', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('pos_p1_y', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('pos_p2_x', 'integer', [
                'default' => 1,
                'null' => false,
            ])
            ->addColumn('pos_p2_y', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('pa_p1', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('pa_p2', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addIndex(['game_id'], ['unique' => true])
            ->addForeignKey('game_id', 'games', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->create();

        $this->table('board_games')->insert([
            [
                'name' => 'Mastermind',
                'type' => 'solo',
            ],
            [
                'name' => 'Filler',
                'type' => 'multiplayer',
            ],
            [
                'name' => 'Labyrinthe',
                'type' => 'multiplayer',
            ],
        ])->saveData();
    }

    public function down(): void
    {
        $this->table('labyrinth_settings')->drop()->save();
        $this->table('filler_settings')->drop()->save();
        $this->table('mastermind_settings')->drop()->save();
        $this->table('users_ingames')->drop()->save();
        $this->table('games')->drop()->save();
        $this->table('users')->drop()->save();
        $this->table('board_games')->drop()->save();
    }
}
