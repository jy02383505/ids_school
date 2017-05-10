<?php
/**
 * author:zjh
 * time:2016-11-11
 * 不需登录验证的公共控制器，所有不需要验证登录的，统一放在此文件
 * 
*/
class UtilityNoLoginAction extends Action {
	
	public function _initialize() {
	}
	public function index(){
		
	}
	
	
	/**
	 * 获取天气，不需后台登录验证，在config.php中已设置不需登录认证
	 *　调用示例：http://school.my.com/ajaxTimer/getWeather/
	 * 用法示例：用定时器定期访问地址：http://192.168.1.2:8000/GetWeatherNoLogin/getWeather/
	*/
	public function getWeather(){
//		$start = date('Y-m-d H:m:s',time());
//		file_put_contents("getWeather.txt",$start."-----",FILE_APPEND);//
		
		$this->getWeatherData();
		
//		$end = date('Y-m-d H:m:s',time());
//		file_put_contents("getWeather.txt",$end.PHP_EOL,FILE_APPEND);//
//		exit;
	}
	
    public function getWeatherData()
    {
        header("Content-Type:text/html;charset=UTF-8");
        $showapi_appid = '2388';
        $showapi_sign = 'f0c69e7fc91c490c8a2c9b7dfd3b801c';
        $showapi_timestamp = date('YmdHis');
        $paramArr = array(
            'showapi_appid' => $showapi_appid,
            'areaid' => '',
            'area' => '大庆',
            'needMoreDay' => '1',
            'needIndex' => '0',
            'showapi_timestamp' => $showapi_timestamp);

        $sign = $this->createSign($paramArr, $showapi_sign);
        $strParam = $this->createStrParam($paramArr);
        $strParam .= 'showapi_sign=' . $sign;
        $url = 'http://route.showapi.com/9-2?' . $strParam;
        $result = file_get_contents($url);
        $result = json_decode($result, true);
        if ($result['showapi_res_code'] != 0) {
            $this->error($result['showapi_res_error']);
        }

        $weatherInfo = $result['showapi_res_body'];
        $weatherModel = M('ReslibWeather');
        $weatherModel->where(1)->delete();
        for ($i = 1; $i <= 7; $i++) {
            $data = array();
            $data['date'] = date('Y-m-d', strtotime($weatherInfo['f' . $i]['day']));
            $data['week_day'] = getWeekday($data['date']);
            $data['weather'] = ($weatherInfo['f' . $i]['day_weather'] != $weatherInfo['f' .
                $i]['night_weather']) ? $weatherInfo['f' . $i]['day_weather'] . '转' . $weatherInfo['f' .
                $i]['night_weather'] : $weatherInfo['f' . $i]['day_weather'];
            $data['temperature'] = ($weatherInfo['f' . $i]['day_air_temperature'] != $weatherInfo['f' .
                $i]['night_air_temperature']) ? $weatherInfo['f' . $i]['day_air_temperature'] .
                '~' . $weatherInfo['f' . $i]['night_air_temperature'] : $weatherInfo['f' . $i]['day_air_temperature'];
            $data['wind'] = $weatherInfo['f' . $i]['day_wind_direction'] . $weatherInfo['f' .
                $i]['day_wind_power'];
            $data['dayPictureUrl'] = basename($weatherInfo['f' . $i]['day_weather_pic']);
            $data['nightPictureUrl'] = basename($weatherInfo['f' . $i]['night_weather_pic']);

            switch ($i) {
                case 1:
                    $data['day_title'] = '今天';
                    break;
                case 2:
                    $data['day_title'] = '明天';
                    break;
                case 3:
                    $data['day_title'] = '后天';
                    break;
                default:
                    $data['day_title'] = '';
            }

            $re = $weatherModel->add($data);
            if (!$re) {
				
                $weatherModel->where(1)->delete();
                //$this->error('数据库写入错误，请检查……');
				echo json_encode(array("stat"=>"0","msg"=>"数据库写入错误","data"=>""));exit;	
            }
		//	file_put_contents("getWeather.txt",PHP_EOL."data:".PHP_EOL.serialize($data).PHP_EOL,FILE_APPEND);//
			
        }
        //$this->success('操作成功！');
		echo json_encode(array("stat"=>"1","msg"=>"更新成功","data"=>""));exit;	
    }	
	
	
    private function createStrParam($paramArr)
    {
        $strParam = '';
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $strParam .= $key . '=' . urlencode($val) . '&';
            }
        }
        return $strParam;
    }


    private function createSign($paramArr, $showapi_sign)
    {
        $sign = "";
        ksort($paramArr);
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $sign .= $key . $val;
            }
        }

        $sign .= $showapi_sign;
        $sign = strtoupper(md5($sign));
        return $sign;
    }	

    /**
     * 使用新的接口采集天气数据，之后入库
     * 城市可以自主切换，只需在url后给出city变量即可，如http://school.cc/UtilityNoLogin/renderWeather/city/北京
     * @return [string] [采集5天的数据，采集成功之后将原库中数据删除]
     */
    public function renderWeather(){
        header("Content-Type:text/html;charset=UTF-8");
        $city = $_GET[city] ? : '太原';
        $first = M()->table('tb_reslib_weather')->find();
        if($first[date] == date('Y-m-d') && $first[city] == $city){
            die('当前数据已是最新！');
        }
        $fromUrl = "http://wthrcdn.etouch.cn/weather_mini?city=$city";
        $result = file_get_contents('compress.zlib://'.$fromUrl);
        $data = json_decode($result, true);
        if($data[status] == '1000'){
            $pattern = '/[^\d*℃-]/';
            $imageDir = '/Public/images/weather/images2/';
            M()->table('tb_reslib_weather')->where('id>=1')->delete();
            foreach($data[data][forecast] as $weather){
                $temp = array();
                $temp[date] = date('Y-m-d', strtotime(date('Y-m-').intval($weather[date])));
                $temp[week_day] = mb_substr($weather[date], -3, 3, 'utf-8');
                if($temp[date] == date('Y-m-d', time())){
                    $temp[day_title] = '今天';
                }else if($temp[date] == date('Y-m-d', time()+3600*24)){
                    $temp[day_title] = '明天';
                }else if($temp[date] == date('Y-m-d', time()+3600*24*2)){
                    $temp[day_title] = '后天';
                }else{
                    $temp[day_title] = '';
                }
                $temp[weather] = $weather[type];
                $temp[wind] = $weather[fengxiang].' '.$weather[fengli];
                $temp[temperature] = preg_replace($pattern, '', $weather[high]).'~'.preg_replace($pattern, '', $weather[low]);
                switch(trim($temp[weather])){
                    case '晴':
                        $temp[dayPictureUrl] = 'fine.png';
                        break;
                    case '阴':
                        $temp[dayPictureUrl] = 'overcast.png';
                        break;
                    case '多云':
                        $temp[dayPictureUrl] = 'cloudy.png';
                        break;
                    case '雷阵雨':
                        $temp[dayPictureUrl] = 'thundershower.png';
                        break;
                    case '阵雨':
                        $temp[dayPictureUrl] = 'shower.png';
                        break;
                    case '小雨':
                        $temp[dayPictureUrl] = 'rain1.png';
                        break;
                    case '小到中雨':
                        $temp[dayPictureUrl] = 'rain2.png';
                        break;
                    case '中雨':
                        $temp[dayPictureUrl] = 'rain2.png';
                        break;
                    case '中到大雨':
                        $temp[dayPictureUrl] = 'rain3.png';
                        break;
                    case '大雨':
                        $temp[dayPictureUrl] = 'rain3.png';
                        break;
                    case '大到暴雨':
                        $temp[dayPictureUrl] = 'rain4.png';
                        break;
                    case '暴雨':
                        $temp[dayPictureUrl] = 'rain4.png';
                        break;
                    case '雨夹雪':
                        $temp[dayPictureUrl] = 'sleet.png';
                        break;
                    case '阵雪':
                        $temp[dayPictureUrl] = 'snowshower.png';
                        break;
                    case '小雪':
                        $temp[dayPictureUrl] = 'snow1.png';
                        break;
                    case '小到中雪':
                        $temp[dayPictureUrl] = 'snow2.png';
                        break;
                    case '中雪':
                        $temp[dayPictureUrl] = 'snow2.png';
                        break;
                    case '中到大雪':
                        $temp[dayPictureUrl] = 'snow3.png';
                        break;
                    case '大雪':
                        $temp[dayPictureUrl] = 'snow3.png';
                        break;
                    case '大到暴雪':
                        $temp[dayPictureUrl] = 'snow4.png';
                        break;
                    case '暴雪':
                        $temp[dayPictureUrl] = 'snow4.png';
                        break;
                    case '雾':
                        $temp[dayPictureUrl] = 'fog.png';
                        break;
                    case '霾':
                        $temp[dayPictureUrl] = 'haze.png';
                        break;
                }
                $temp[city] = $city;

                if($model = M()->table('tb_reslib_weather')->add($temp)){
                    $r++;
                }
            }
            if($r == 5){
                echo '天气数据更新成功！';
            }
        }
    }
	
}