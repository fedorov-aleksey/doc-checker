<?php
/**
 * Created by PhpStorm.
 * User: fav
 * Date: 06.12.16
 * Time: 9:52
 */

namespace fav\doc;


class validator
{
    public static function inn($inn, $resident = 1, $date = '01.01.2004')
    {

        if ($resident == 0 && strlen($inn) == 10 && strpos($inn, '9909') !== 0 && date('Ymd', strtotime($date)) >= '20050101') { //инн иностранных юр. лиц должен начинаться с 9909 после 2005-01-01
            return false;
        } elseif ($resident == 0 && strlen($inn) == 10 && strpos($inn, '9909') === 0 && date('Ymd', strtotime($date)) < '20050101') { //инн иностранных юр. лиц должен не начинаться с 9909 до 2005-01-01
            return false;
        } elseif ($resident == 1 && strlen($inn) == 10 && strpos($inn, '9909') === 0) {
            return false;
        } elseif (strlen($inn) < 10 or strlen($inn) > 12) {
            return false;
        }

        if (strlen(preg_replace('/[^0-9]/', '', $inn)) != strlen($inn))
            return false;
        $ur = 0;
        if (substr($inn, 0, 10) == '0000000000')
            return false;
        $C = array("C" => array(7, 2, 4, 10, 3, 5, 9, 4, 6, 8, 0, -2),
            "D" => array(3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8, -1));
        $result = true;
        if (strlen($inn) == 10) {
            $inn = '0' . $inn . '0';
            $ur = 1;
        } else if (strlen($inn) == 11) {
            $inn = '0' . $inn;
            $ur = 2;
        }
        foreach ($C as $k => $row) {
            $sum = 0;
            for ($i = 0; $i < 11; $i++) {
                $sum += $row[$i] * (int)(substr($inn, $i, 1));
            }
            $contr_sum = $sum % 11;
            if ($contr_sum == 10)
                $contr_sum = 0;
            if ($contr_sum != (int)substr($inn, $row[11], 1))
                return false;
            if ($ur != 0)
                break;
        }
        return true;
    }

    public static function okpo($okpo)
    {
        $okpo_length = strlen($okpo);
        $control = substr($okpo, -1);
        if ($okpo_length == 8 || $okpo_length == 10) {
            $tmp = 0;
            for ($i = 0; $i < $okpo_length - 1; $i++) {
                $tmp += substr($okpo, $i, 1) * ($i + 1);
            }
            if ($tmp % 11 == 10) {
                $tmp = 0;
                for ($i = 0; $i < $okpo_length - 1; $i++) {
                    $tmp += substr($okpo, $i, 1) * ($i + 3);
                }
            }
            if (
                $tmp % 11 == $control
                || $tmp % 11 == 10 && $control == 0
            ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function egrn($value)
    {

        if (!is_numeric($value) || $value == '0000000000000') {
            return false;
        }
        if (strlen($value) == 13) {
            $check = substr($value, 0, 12);
            $checkValue = $check % 11;
            $controlValue = substr($value, 12);
        } elseif (strlen($value) == 15) {
            $check = substr($value, 0, 14);
            $checkValue = ($check % 13);
            $controlValue = substr($value, 14);
        } else {
            return false;
        }
        if ($checkValue >= 10) {
            $checkValue -= 10;
        }
        if ($checkValue == $controlValue) {
            return true;
        } else {
            return false;
        }
    }

    public static function pfno($pfno)
    {
        if (substr($pfno, 0, 11) == '00000000000') {
            return false;
        }

        $sum = 0;
        $result = true;

        if ((int)substr($pfno, 0, 9) > (int)'001001998') {
            for ($i = 0; $i < 9; $i++) {
                $sum += ((9 - $i) * (int)(substr($pfno, $i, 1)));
            }
            if ($sum == 99) {
                $contr_sum = 99;
            } else if ($sum == 100) {
                $contr_sum = 00;
            } else if ($sum == 101) {
                $contr_sum = 00;
            } else if ($sum == 102) {
                $contr_sum = 01;
            } else {
                $contr_sum = $sum % 101;
                if (strlen($contr_sum) < 2)
                    $contr_sum = "0" . $contr_sum;
                $contr_sum = substr($contr_sum, -2);
            }
            if ($contr_sum != (int)substr($pfno, 9, 2)) {
                $result = false;
            }

            unset($sum);
            unset($contr_sum);
            unset($i);
        } else {
            return false;
        }

        return $result;
    }

    public static function doctype_is_russian($doctype)
    {
        if ($doctype != (int)$doctype || strlen($doctype) == 0) {
            return false;
        } elseif (
            $doctype > 0 && $doctype < 6
            || $doctype > 12 && $doctype < 15
        ) {
            return true;
        } else {
            return false;
        }
    }

    public static function pboul($pboul)
    {
        if ($pboul == "000000000000000") {
            return false;
        }

        $control = fmod((substr($pboul, 0, 14) + 0), 13);

        if ($control >= 10) {
            $control = $control - 10;
        }

        if (substr($pboul, 14, 1) == $control) {
            return true;
        } else {
            return false;
        }
    }

    public static function check_doc($docno, $doctype)
    {
        $exception = array(
            '4' => array('АА1111111' => true)
        );
        if (isset($exception[(int)$doctype][strtoupper($docno)])) {
            return false;
        }
        $docno = str_replace(array(' ', '-', '\\', '/', ';', '='), "", $docno);
        if ($doctype == 1) { // Паспорт РФ
            if (preg_match("|^[0-9]{10}$|i", $docno)) {
                $arr = array('00', '02', '06', '13', '16', '21', '23', '31', '35'); //несуществующие серии
                $temp = array_search(substr($docno, 0, 2), $arr);
                unset($arr);
                if (($temp === NULL) or $temp === false) {
                    unset($temp);
                    if (!strncmp(php_uname('n'), 'vld8-bki-test', 13) == 0) { // на тесте для тестовых заемщиков допустип номер паспорта =000000
                        if (substr($docno, 4, 6) != '000000') {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return true;
                    }
                } else {
                    unset($temp);
                    return false;
                }
            } else {
                return false;
            }
        } else if ($doctype == 2) { // Свидетельство о рождении
            if (preg_match("|^[MDCLXVI]{1,6}[А-ЯЁ]{2}[0-9]{6}$|iu", $docno)) //от 1 до 6 римских цифр, 2 русские буквы, 6 цифр
                return true;
            else {
                return false;
            }
        } else if ($doctype == 3) { // удостоверение для офицеров, прапорщиков, мичманов
            if (preg_match('%^[А-ЯЁ]{2}[0-9]{6,7}$%iu', $docno)) //2 буквы + 6-7 цыфр
                return true;
            else {
                return false;
            }
        } else if ($doctype == 4) { // военный билет
            if (preg_match('%^[А-ЯЁ]{2}[0-9]{6,7}$%iu', $docno)) //2 буквы + 6-7 цыфр
                return true;
            else
                return false;
        } else if ($doctype == 5) { // паспорт моряка
            if (preg_match("|^[А-ЯЁ]{2}[0-9]{7}$|u", $docno)) //2 буквы + 7 цыфр
                return true;
            else
                return false;
        } else if ($doctype == 6) { //Паспорт иностранного гражданина либо иной документ, установленный федеральным законом или признаваемый в соответствии с международным договором Российской Федерации в качестве документа, удостоверяющего личность иностранного гражданина
            if (preg_match("|^[А-ЯЁ0-9A-Z]{1,20}$|u", $docno))
                return true;
            else
                return false;
        } else if ($doctype == 7) { //Документ, выданный иностранным государством и признаваемый в соответствии с международным договором Российской Федерации в качестве документа, удостоверяющего личность лица без гражданства
            if (preg_match("|^[А-ЯЁ0-9A-Z]{1,20}$|iu", $docno))
                return true;
            else
                return false;
        } else if ($doctype == 8) { // разрешение на временное проживание
            if (preg_match("|^[0-9]{8}$|iu", $docno)) //8 цыфр
                return true;
            else
                return false;
        } else if ($doctype == 9) { //вид на жительство
            if (preg_match("|^[0-9]{9}$|u", $docno)) //9 цыфр
                return true;
            else
                return false;
        } else if ($doctype == 10) { //Иные документы, предусмотренные федеральным законом или признаваемые в соответствии с международным договором Российской Федерации в качестве документов, удостоверяющих личность лица без гражданства
            if (preg_match("|^[А-ЯЁ0-9A-Z]{1,20}$|iu", $docno))
                return true;
            else
                return false;
        } else if ($doctype == 11) { //Свидетельство о регистрации ходатайства о признании иммигранта беженцем
            if (preg_match("|^[А-ЯЁ0-9A-Z]{1,20}$|iu", $docno))
                return true;
            else
                return false;
        } else if ($doctype == 12) { // удостоверение беженца
            if (preg_match("|^[А-ЯЁ0-9A-Z]{1,20}$|iu", $docno))
                return true;
            else
                return false;
        } else if ($doctype == 13) { //Временное удостоверение личности гражданина
            if (preg_match("|^[А-ЯЁ0-9A-Z]{1,20}$|iu", $docno))
                return true;
            else
                return false;
        } else if ($doctype == 14) { //Иные документы, выдаваемые уполномоченными органами
            if (preg_match("|^[А-ЯЁ0-9A-Z]{1,20}$|iu", $docno))
                return true;
            else
                return false;
        } else if ($doctype == 15) { // загранпаспорт
            if (preg_match("|^[0-9]{9}$|iu", $docno))
                return true;
            else
                return false;
        } else if ($doctype == 16) { // водительское удостоверение
            if (preg_match("|^[0-9]{2}[А-ЯЁ]{2}[0-9]{6}$|iu", $docno))
                return true;
            else
                return false;
        } else
            return true;
    }
}