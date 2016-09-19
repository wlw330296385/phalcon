<?php
namespace Oupula\Library;
use Exception;
class IpLocation
{
    private $database_path = '';
    private $database_filename = '';
    private $database_realpath = '';
    public function __construct()
    {

        $this->database_path = realpath(sprintf('%s../common/ipdata/',APP_PATH)).'/';
        $this->database_filename = 'qqwrt.dat';
        $this->database_realpath = sprintf('%s%s',$this->database_path,$this->database_filename);
    }



    /**
     * 根据所给 IP 地址或域名返回所在地区信息
     *
     * @access public
     * @param string $ip
     * @return array
     */
    public function getlocation($ip = ''){
        if(empty($ip)){
            $ip = '127.0.0.1';
        }
        return $this->_qqrtGetIP($ip);
    }

    /**
     * QQWRT IP库查询IP所在地
     * @param string $ip
     * @return mixed
     * @throws \Exception
     */
    private function _qqrtGetIP($ip = ''){
            $ipDataFile = $this->database_path . 'qqwry.dat';
            if (!$fd = @fopen($ipDataFile, 'rb')) {
                throw new Exception('qqwry.dat IP库不存在');
            }
            $ip = explode('.', $ip);
            $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];
            if (!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4))) {
                return false;
            }
            @$ipBegin = implode('', unpack('L', $DataBegin));
            if ($ipBegin < 0) {
                $ipBegin += pow(2, 32);
            }
            @$ipEnd = implode('', unpack('L', $DataEnd));
            if ($ipEnd < 0) {
                $ipEnd += pow(2, 32);
            }
            $ipAllNum = ($ipEnd - $ipBegin) / 7 + 1;
            $BeginNum = $ip2num = $ip1num = 0;
            $ipAddress1 = $ipAddress2 = '';
            $EndNum = $ipAllNum;
            // 二分法查找 IP
            while ($ip1num > $ipNum || $ip2num < $ipNum) {
                $Middle = intval(($EndNum + $BeginNum) / 2);
                fseek($fd, $ipBegin + 7 * $Middle);
                $ipData1 = fread($fd, 4);
                if (strlen($ipData1) < 4) {
                    fclose($fd);
                    return 0;
                }
                $ip1num = implode('', unpack('L', $ipData1));
                if ($ip1num < 0) {
                    $ip1num += pow(2, 32);
                }
                if ($ip1num > $ipNum) {
                    $EndNum = $Middle;
                    continue;
                }
                $DataSeek = fread($fd, 3);
                if (strlen($DataSeek) < 3) {
                    fclose($fd);
                    return 0;
                }
                $DataSeek = implode('', unpack('L', $DataSeek . chr(0)));
                fseek($fd, $DataSeek);
                $ipData2 = fread($fd, 4);
                if (strlen($ipData2) < 4) {
                    fclose($fd);
                    return 0;
                }
                $ip2num = implode('', unpack('L', $ipData2));
                if ($ip2num < 0) {
                    $ip2num += pow(2, 32);
                }
                if ($ip2num < $ipNum) {
                    if ($Middle == $BeginNum) {
                        fclose($fd);
                        return '- Unknown';
                    }
                    $BeginNum = $Middle;
                }
            }
            // 模式判断
            $ipFlag = fread($fd, 1);
            //模式 1
            if ($ipFlag == chr(1)) {
                $ipSeek = fread($fd, 3);
                if (strlen($ipSeek) < 3) {
                    fclose($fd);
                    return 0;
                }
                $ipSeek = implode('', unpack('L', $ipSeek . chr(0)));
                fseek($fd, $ipSeek);
                $ipFlag = fread($fd, 1);
            }
            // 模式 2
            if ($ipFlag == chr(2)) {
                $AddressSeek = fread($fd, 3);
                if (strlen($AddressSeek) < 3) {
                    fclose($fd);
                    return 0;
                }
                $ipFlag = fread($fd, 1);
                if ($ipFlag == chr(2)) {
                    $AddressSeek2 = fread($fd, 3);
                    if (strlen($AddressSeek2) < 3) {
                        fclose($fd);
                        return 0;
                    }
                    $AddressSeek2 = implode('', unpack('L', $AddressSeek2 . chr(0)));
                    fseek($fd, $AddressSeek2);
                } else {
                    fseek($fd, -1, SEEK_CUR);
                }
                while (($char = fread($fd, 1)) != chr(0)) {
                    $ipAddress2 .= $char;
                }
                $AddressSeek = implode('', unpack('L', $AddressSeek . chr(0)));
                fseek($fd, $AddressSeek);
                while (($char = fread($fd, 1)) != chr(0)) {
                    $ipAddress1 .= $char;
                }
                // 其他模式
            } else {
                fseek($fd, -1, SEEK_CUR);
                while (($char = fread($fd, 1)) != chr(0)) {
                    $ipAddress1 .= $char;
                }
                $ipFlag = fread($fd, 1);
                if ($ipFlag == chr(2)) {
                    $AddressSeek2 = fread($fd, 3);
                    if (strlen($AddressSeek2) < 3) {
                        fclose($fd);
                        return 0;
                    }
                    $AddressSeek2 = implode('', unpack('L', $AddressSeek2 . chr(0)));
                    fseek($fd, $AddressSeek2);
                } else {
                    fseek($fd, -1, SEEK_CUR);
                }
            }
            fclose($fd);
            $ipAddress = preg_replace('/^\s*/is', '', $ipAddress1);
            $ipAddress = preg_replace('/\s*$/is', '', $ipAddress);
            if (preg_match('/http/i', $ipAddress) || $ipAddress == '') {
                $ipAddress = '- Unknown';
            }
            if(function_exists('iconv')){
                return iconv('GBK','UTF-8//IGNORE',$ipAddress);
            }else{
                if(function_exists('mb_convert_encoding')){
                    return mb_convert_encoding($ipAddress,'UTF-8','GBK');
                }else{
                    throw new Exception("需要开启 mbstring / iconv 扩展");
                }
            }
    }

    /**
     * 检查IP地址是否在指定IP段内
     * @param $checkIP
     * @param $limitIP
     * @return bool
     */
    public function checkIP($checkIP, $limitIP)
    {
        if (empty($limitIP)) {
            return true;
        }
        $arrCheckIP = explode('.', $checkIP);
        $arrLimitIP = explode('.', $limitIP);
        for ($i = 0; $i < count($arrLimitIP); $i++) {
            if ($arrLimitIP[$i] != '*' && $arrLimitIP[$i] != '0' && $arrLimitIP[$i] != '255') {
                if ($arrCheckIP[$i] != $arrCheckIP[$i]) {
                    return false;
                }
            }
        }
        return true;
    }


}