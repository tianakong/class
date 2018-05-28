<?php
/**
 * 短网址算法：高进位 62^6
 * @author tiankong <tianakong@aliyun.com>
 * @version 1.0
 */

class url
{
    ///个位对比数组（第一次用 echo url::make_contrast(); 生成，以后不可更改）
    protected static $_super2dec_arr = array('m' => 0, 'T' => 1, 'g' => 2, 'B' => 3, '2' => 4, 'L' => 5, 'x' => 6, 'X' => 7, 'Z' => 8, '5' => 9, 'd' => 10, 'b' => 11, 'l' => 12,
        'f' => 13, 'K' => 14, 'n' => 15, '7' => 16, 'S' => 17, 'Y' => 18, 'j' => 19, 'R' => 20, 'H' => 21, 'U' => 22, 'q' => 23, 'a' => 24, 'p' => 25, 'z' => 26, 'u' => 27, 'o' => 28, 'v' => 29,
        'E' => 30, 'V' => 31, 'I' => 32, '6' => 33, 'F' => 34, 'Q' => 35, 'i' => 36, '8' => 37, '3' => 38, 'N' => 39, 'O' => 40, 'D' => 41, 'k' => 42, 'P' => 43, 'A' => 44, '0' => 45, 'c' => 46,
        't' => 47, '4' => 48, 's' => 49, 'G' => 50, 'w' => 51, 'C' => 52, 'h' => 53, 'J' => 54, 'W' => 55, 'e' => 56, 'M' => 57, '1' => 58, 'y' => 59, 'r' => 60, '9' => 61);

    protected static $_dec2super_arr = array(0 => 'm', 1 => 'T', 2 => 'g', 3 => 'B', 4 => '2', 5 => 'L', 6 => 'x', 7 => 'X', 8 => 'Z', 9 => '5', 10 => 'd', 11 => 'b', 12 => 'l',
        13 => 'f', 14 => 'K', 15 => 'n', 16 => '7', 17 => 'S', 18 => 'Y', 19 => 'j', 20 => 'R', 21 => 'H', 22 => 'U', 23 => 'q', 24 => 'a', 25 => 'p', 26 => 'z', 27 => 'u', 28 => 'o', 29 => 'v',
        30 => 'E', 31 => 'V', 32 => 'I', 33 => '6', 34 => 'F', 35 => 'Q', 36 => 'i', 37 => '8', 38 => '3', 39 => 'N', 40 => 'O', 41 => 'D', 42 => 'k', 43 => 'P', 44 => 'A', 45 => '0', 46 => 'c',
        47 => 't', 48 => '4', 49 => 's', 50 => 'G', 51 => 'w', 52 => 'C', 53 => 'h', 54 => 'J', 55 => 'W', 56 => 'e', 57 => 'M', 58 => '1', 59 => 'y', 60 => 'r', 61 => '9');

    ///进制数（最大为62, 因为0-9a-zA-Z只有这么多）
    protected static $_digit = 62;

    ///随机加密最小位数
    public static $min_slen = 16;

    ///随机加密最大位数
    public static $max_slen = 32;

    /**
     * 生成个位对比位数数组 
     */
    public static function make_contrast()
    {
        $_chars = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $chars = substr($_chars, 0, self::$_digit);
        $super2dec_arr = 'protected static $_super2dec_arr = array(';
        $dec2super_arr = 'protected static $_dec2super_arr = array(';
        for ($i = 0; $i < self::$_digit; $i++) {
            $c = $chars[mt_rand(1, strlen($chars)) - 1];
            $chars = str_replace($c, '', $chars);
            $super2dec_arr .= "'{$c}'=>{$i},";
            $dec2super_arr .= "{$i}=>'{$c}',";
        }
        $super2dec_arr .= ');';
        $dec2super_arr .= ');';
        return $super2dec_arr . "\n" . $dec2super_arr;
    }

    /**
     * 超进位数和十进位数的个位对照数
     * @parem string $number
     * @return int | false
     */
    private static function _super2dec_base($number)
    {
        return isset(self::$_super2dec_arr[$number]) ? self::$_super2dec_arr[$number] : false;
    }

    /**
     * 十进位数和超进位数的个位对照数
     * @parem int $number
     * @return string | false
     */
    private static function _dec2super_base($number)
    {
        return isset(self::$_dec2super_arr[$number]) ? self::$_dec2super_arr[$number] : false;
    }

    /**
     * 超进位数转换为十进位数
     * @parem string $number
     * @return int | false
     */
    public static function get_dec($number)
    {
        $renum = 0;
        $nlength = strlen($number) - 1;
        $j = 0;
        for ($i = $nlength; $i > -1; $i--) {
            if (self::_super2dec_base($number[$i]) === false) {
                return false;
            }
            $renum += self::_super2dec_base($number[$i]) * pow(self::$_digit, $j);
            $j++;
        }
        return $renum;
    }

    /**
     * 十进位数转为超进位数
     * @parem int $number
     * @return string | false
     */
    public static function get_super($number)
    {
        $renum = '';
        if (empty($number)) {
            return self::_dec2super_base(0);
        }
        $_loop = 0;
        while (true) {
            $_loop++;
            $next_dd = floor($number / self::$_digit);
            $cur_limit = $number % self::$_digit;
            if ($next_dd == 0) {
                if ($cur_limit != 0) {
                    if (self::_dec2super_base($cur_limit) === false) {
                        return false;
                    }
                    $renum = self::_dec2super_base($cur_limit) . $renum;
                }
                break;
            } else {
                if (self::_dec2super_base($cur_limit) === false) {
                    return false;
                }
                $renum = self::_dec2super_base($cur_limit) . $renum;
                $number = $next_dd;
            }
            if ($_loop > 20) {
                return false;
            }
        }
        return $renum;
    }

    /**
     * 超进位数转换为十进位数（加密模式--超数第一位为进位标识）
     * @parem string $number
     * @return int | false
     */
    public static function get_dec_s($number)
    {
        $dd = $number[0];
        if (self::_super2dec_base($dd) === false) {
            return false;
        }
        self::$_digit = self::_super2dec_base($dd) + 1;
        return self::get_dec(preg_replace('/^' . $dd . '/', '', $number));
    }

    /**
     * 十进位数转为超进位数（加密模式--随机选取数据位数进行加密，并在第一位增加位数标识）
     * @parem int $number
     * @return string | false
     */
    public static function get_super_s($number)
    {
        self::$_digit = mt_rand(self::$min_slen, self::$max_slen);
        if (self::_dec2super_base(self::$_digit - 1) === false) {
            return false;
        }
        $snum = self::_dec2super_base(self::$_digit - 1) . self::get_super($number);
        return $snum;
    }

    /**
     * 获得一个和时间关连的12-x位唯一标识符
     * @parem $ulen 字符串长度（最小为12位）
     * @return string
     */
    public static function get_unique($ulen = 15, $germ = '')
    {
        //按每百万位数取62进制转字符串（会转换为4位字符）
        $plen = 7;
        self::$_digit = 62;
        if (empty($germ)) {
            //种子一，毫秒数数字去除 . ，不足14位随机补数字
            $germ1 = str_replace('.', '', sprintf('%0.4f', microtime(true)));
            while (strlen($germ1) < 14) {
                $germ1 .= mt_rand(0, 9);
            }
            //种子二，百万间的随机数
            $germ2 = mt_rand(1000000, 9999999);

            //按每10亿位数取62进制转字符串
            $germ = $germ1 . $germ2;
        }
        $glen = strlen($germ);
        $estr = '';
        $i = 0;
        while ($i < $glen) {
            $t = substr($germ, $i, $plen);
            $estr .= self::get_super(intval($t));
            $i += $plen;

        }
        while ($ulen > 0 && strlen($estr) < $ulen) {
            $estr = self::get_super(mt_rand(0, self::$_digit - 1)) . $estr;
        }
        return $estr;
    }

}
