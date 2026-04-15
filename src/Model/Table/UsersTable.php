<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setPrimaryKey('id');
        $this->hasMany('UsersIngames');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('username')
            ->maxLength('username', 100)
            ->requirePresence('username', 'create')
            ->notEmptyString('username');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('password')
            ->minLength('password', 4)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        $validator
            ->add('username', 'unique', [
                'rule' => function ($value, $context) {
                    $conditions = ['username' => $value];
                    if (!empty($context['data']['id'])) {
                        $conditions['id !='] = $context['data']['id'];
                    }

                    return $this->find()->where($conditions)->count() === 0;
                },
                'message' => 'Ce nom d’utilisateur existe déjà.',
            ])
            ->add('email', 'unique', [
                'rule' => function ($value, $context) {
                    $conditions = ['email' => $value];
                    if (!empty($context['data']['id'])) {
                        $conditions['id !='] = $context['data']['id'];
                    }

                    return $this->find()->where($conditions)->count() === 0;
                },
                'message' => 'Cet email existe déjà.',
            ]);

        return $validator;
    }

    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        foreach (['username', 'email'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }
    }

    public function beforeSave(EventInterface $event, object $entity, ArrayObject $options): void
    {
        if ($entity->isDirty('password')) {
            $entity->password = password_hash((string)$entity->password, PASSWORD_DEFAULT);
        }
    }
}
