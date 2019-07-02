<?php declare(strict_types=1);

namespace Casoa\Yii\Utility;

use yii\helpers\Json;
use yii\validators\DateValidator;

class ModelRuleUtility extends Utility
{
    /**
     * 过滤字符串值
     * @param mixed $value
     * @return null|string
     */
    public static function filterString($value): ?string
    {
        if (is_string($value)) {
            $value = trim($value);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * 过滤字符串值
     * @param mixed $value
     * @return null|string
     */
    public static function filterStringWithoutTrim($value): ?string
    {
        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /**
     * 过滤JSON字符串值
     * @param mixed $value
     * @return null|string
     */
    public static function filterJsonString($value): ?string
    {
        if (is_array($value)) {
            return Json::encode($value);
        }

        if (is_string($value)) {
            $json = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return Json::encode($json);
            }
        }

        return null;
    }

    /**
     * 过滤数值
     * @param mixed $value
     * @return null|int
     */
    public static function filterInt($value): ?int
    {
        if (is_numeric($value)) {
            return (int)$value;
        }

        return null;
    }

    /**
     * 过滤布尔值
     * @param mixed $value
     * @return null|bool
     */
    public static function filterBool($value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === 0 || $value === '0') {
            return false;
        }

        if ($value === 1 || $value === '1') {
            return true;
        }

        return null;
    }

    /**
     * 过滤日期
     * @param $value
     * @param mixed $format
     * @return null|string
     */
    public static function filterTimestamp($value, string $format = 'Y-m-d H:i:s'): ?string
    {
        if (is_string($value) && trim($value) !== '') {
            $validator = new DateValidator(['format' => "php:$format"]);
            if ($validator->validate($value)) {
                return $value;
            }

            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return date($format, $timestamp);
            }
        }

        return null;
    }
}
