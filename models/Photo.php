<?php

namespace rere\image\models;

use Yii;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%photo}}".
 *
 * @property string $id
 * @property string $sort_id
 * @property string $owner_id
 * @property integer $model
 * @property string $type
 * @property string $name
 * @property string $width
 * @property string $height
 * @property string $about
 * @property string $cropParams
 * @property string $hash
 * @property string $updated_at
 * @property string $created_at
 */
class Photo extends \yii\db\ActiveRecord
{
    public static $path = '/image/tmp';
    public $file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%photo}}';
    }

    public static function add($name, $params)
    {
        $defaultParams = [
            'type' => 'main',
        ];
        $params['name'] = $name;
        $model = new self;
        $model->setAttributes(array_merge($defaultParams, $params));
        return $model->save();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['sort_id', 'width', 'height'], 'integer'],
            [['updated_at', 'created_at'], 'safe'],
            [['type'], 'string', 'max' => 8],
            [['name', 'about', 'cropParams'], 'string', 'max' => 255],
            [['hash'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('rere.model', 'ID'),
            'sort_id' => Yii::t('rere.model', 'Sort ID'),
            'type' => Yii::t('rere.model', 'Type'),
            'name' => Yii::t('rere.model', 'Name'),
            'width' => Yii::t('rere.model', 'Width'),
            'height' => Yii::t('rere.model', 'Height'),
            'about' => Yii::t('rere.model', 'About'),
            'cropParams' => Yii::t('rere.model', 'Crop Params'),
            'hash' => Yii::t('rere.model', 'Hash'),
            'updated_at' => Yii::t('rere.model', 'Updated At'),
            'created_at' => Yii::t('rere.model', 'Created At'),
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert && ($file = $this->getFile(true))) {
            if (file_exists($file)) {
                list($this->width, $this->height) = getimagesize($file);
                $this->hash = md5_file($file);
            } else throw new Exception('File not found in tmp dir ' . $file);
            if (isset(Yii::$app->user) && Yii::$app->user->id)
                $this->user_id = Yii::$app->user->id;
        }

        return parent::beforeSave($insert);
    }

    public function getFile($global = false)
    {
        return $this->name ? Yii::getAlias(($global ? '@webroot' : '') . self::$path . DIRECTORY_SEPARATOR . $this->name) : null;
    }

    public function getHref($type, $scheme = false)
    {
        return Url::to(['/image/index', 'type' => $type, 'name' => $this->name], $scheme);
    }

    public function beforeDelete()
    {
        if ($fileName = $this->name)
            FileHelper::findFiles(Yii::getAlias('@webroot/image'), ['filter' => function ($file) use ($fileName) {
                if (basename($file) == $fileName) unlink($file);
                return is_dir($file);
            }, 'recursive' => true]);

        return parent::beforeDelete();
    }
}
