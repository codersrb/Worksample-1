<?php
namespace common\components;

use yii;
use yii\helpers\Url;


class ApiHelper
{
    /**
     * Upload image method for Android
     * @param string $varImage (required) Base64 Encoded Image
     * @param string $varImageName (required) imageName
     * @param string $varPath (required) Path to save image
     * @return mixed array on success, false on fail
     */
    public static function uploadImageFromB64($varImage, $varImageName, $varPath, $ext = '.png', $deleteImageName = false)
    {
        if($varImage)
        {
            $image_data = str_replace(' ',  '+',  $varImage);
            $image_data = base64_decode($image_data);
            
            $name =  \yii\helpers\Inflector::slug($varImageName).$ext;
            $file = Url::to('@frontend').'/web'.Yii::$app->params[$varPath].$name;
            if(file_put_contents($file, $image_data))
            {
                if($deleteImageName)
                {
                    @unlink(Url::to('@frontend').'/web'.Yii::$app->params[$varPath].$deleteImageName);
                }

                $data['name'] = $name;
                $data['url'] = Url::base(true).Yii::$app->params[$varPath].$name;

                return $data;
            }

            return false;
        }
    }
}
