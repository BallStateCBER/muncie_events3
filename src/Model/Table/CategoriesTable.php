<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Categories Model
 *
 * @property \Cake\ORM\Association\HasMany $Events
 * @property \Cake\ORM\Association\BelongsToMany $MailingList
 */
class CategoriesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('categories');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->hasMany('Events', [
            'foreignKey' => 'category_id'
        ]);
        $this->belongsToMany('MailingList', [
            'foreignKey' => 'category_id',
            'targetForeignKey' => 'mailing_list_id',
            'joinTable' => 'categories_mailing_list'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->requirePresence('slug', 'create')
            ->notEmpty('slug');

        $validator
            ->integer('weight')
            ->requirePresence('weight', 'create')
            ->notEmpty('weight');

        return $validator;
    }

    /**
     * getName
     *
     * @param int $id of user whose name we want
     * @return string
     */
    public function getName($id)
    {
        $result = $this->find()
            ->select('name')
            ->where(['id' => $id])
            ->first();
        if (empty($result)) {
            return false;
        }

        return $result['name'];
    }
}
