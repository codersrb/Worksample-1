<?php
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');

Yii::setAlias('@fronturl', 'http://localhost/projects/frameworks/yii/anonymized');
Yii::setAlias('@backurl', 'http://localhost/projects/frameworks/yii/anonymized/anonymized-backend');
