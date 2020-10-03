<?php

namespace backend\models;

use Yii;
use yii\helpers\Url;

/**
 * Profile is the model behind the contact form.
 */
class Profile extends \yii\db\ActiveRecord
{
    public $confirm_password;
    public $imageFile;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['userName', 'userEmail'], 'required'],
            // email has to be a valid email address
            ['userEmail', 'email'],
            ['confirm_password', 'compare', 'compareAttribute' => 'userPassword'],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
        ];
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'userPassword' => 'Password',
            'confirm_password' => 'Confirm Password',
            'imageFile' => 'Profile Picture',
        ];
    }



    /**
     * Upload Image method
     */
    public function upload()
    {
        if($this->validate())
        {
            if($this->save())
            {
                if($this->imageFile)
                {

                    // Path Configs
                    $imageBase = Url::to('@frontend').'/web'.Yii::$app->params['profileImagePath'];
                    $imageName = \yii\helpers\Inflector::slug($this->userName.'-'.Yii::$app->security->generateRandomString()).'.'.$this->imageFile->extension;
                    $imageFile = $imageBase.$imageName;

                    // Delete Old Images
                    $varOldImage = $imageBase.$this->userProfilePicture;
                    if(file_exists($varOldImage))
                    {
                        @unlink($varOldImage);
                    }

                    // Save New Image
                    $this->userProfilePicture = $imageName;
                    $this->save();

                    if($this->imageFile->saveAs($imageFile))
                    {
                        @chmod($imageFile, 0755);
                        return true;
                    }
                }

                return true;
            }
        }
        return false;
    }


}
