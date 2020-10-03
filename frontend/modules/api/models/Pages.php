<?php

namespace frontend\modules\api\models;

use Yii;

/**
 * This is the model class for table "{{%pages}}".
 *
 * @property integer $pkPageID
 * @property string $pageSlug
 * @property string $pageTitle
 * @property string $pageContent
 * @property string $pageExcerpt
 * @property string $pageStatus
 * @property string $pageAdded
 * @property string $pageModified
 */
class Pages extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pages}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pageSlug', 'pageTitle', 'pageContent', 'pageStatus', 'pageAdded'], 'required'],
            [['pageContent', 'pageExcerpt', 'pageStatus'], 'string'],
            [['pageAdded', 'pageModified'], 'safe'],
            [['pageSlug', 'pageTitle'], 'string', 'max' => 255],
            [['pageSlug'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pkPageID' => 'Page ID',
            'pageSlug' => 'Slug',
            'pageTitle' => 'Title',
            'pageContent' => 'Content',
            'pageExcerpt' => 'Excerpt',
            'pageStatus' => 'Status',
            'pageAdded' => 'Added On',
            'pageModified' => 'Modified On',
        ];
    }
}
