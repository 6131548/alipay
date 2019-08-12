<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2019 广东卓锐软件有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------

namespace app\index\controller;
use think\Db;
use think\facade\Env;
/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Alipay extends Home
{	
	/**
	 * [Recharge 付款]
	 * @param [type] $num [金额]
	 */
	public function Recharge($num=null){
		$order_no  = generateChargeOrderNo();
		$Uid = session('home_user_auth')['id'] ;
		//生成订单
		// $res = $this->AddChargeOrder($order_no,$Uid,$num);
		// if(!$res){
		// 	return $this->error('系统繁忙,订单生成失败!');
		// }
		
		$subject = '充值';
		$body = '充值';
		$this->pay($order_no,$num,$subject,$body) ;
	}

	/**
	 * [pay 生成统一下单]
	 * @param  [type] $out_trade_no [订单号码]
	 * @param  [type] $total_amount [总价]
	 * @param  [type] $subject      [描述]
	 * @param  [type] $body         [主题]
	 * @return [type]               [description]
	 */
	protected function pay($out_trade_no,$total_amount,$subject,$body){
		require  Env::get('root_path').'extend/wapalipay/wappay/service/AlipayTradeService.php';
		require Env::get('root_path').'extend/wapalipay/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
	    $config = config('aliyun');
	    
	    $timeout_express="1m";
	    $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
	    $payRequestBuilder->setBody($body);
	    $payRequestBuilder->setSubject($subject);
	    $payRequestBuilder->setOutTradeNo($out_trade_no);
	    $payRequestBuilder->setTotalAmount($total_amount);
	    $payRequestBuilder->setTimeExpress($timeout_express);
	    $payResponse = new \AlipayTradeService($config);
	    $result = $payResponse->wapPay($payRequestBuilder,$config['return_url'],$config['notify_url']);
	    return ;

	}

	/**

	 * @function    alipayNotifyCallback

	 * @intro       	支付宝回调

	 * @return  string

	 */

	public function alipayNotifyCallback()
	{
		// alipayNotifyCallBackLog(json_encode($_POST));//这里是记录日志（可选）
		require Env::get('root_path') .'extend/alipay/AlipayTradePagePay/pagepay/service/AlipayTradeService.php';
		$config    = config('aliyun');
		$aop       = new \AlipayTradeService($config);
		$result = $aop->check($_POST);
		if($result == 1){
		    if($_POST['trade_status'] == 'TRADE_SUCCESS' || $_POST['trade_status'] == 'TRADE_FINISHED'){//付款成功
		    //保存支付宝返回的信息
			$alipayData = [];
			$alipayData['trade_no'] = $_POST['trade_no'];
			$alipayData['app_id'] = $_POST['app_id'];
			$alipayData['out_trade_no'] = $_POST['out_trade_no'];
			$alipayData['out_biz_no'] = isset($_POST['out_biz_no']) ? $_POST['out_biz_no'] : '';
			$alipayData['buyer_id'] = isset($_POST['buyer_id']) ? $_POST['buyer_id'] : '';
			$alipayData['seller_id'] = isset($_POST['seller_id']) ? $_POST['seller_id'] : '';
			$alipayData['trade_status'] = isset($_POST['trade_status']) ? $_POST['trade_status'] : '';
			$alipayData['total_amount'] = isset($_POST['total_amount']) ? $_POST['total_amount'] : '';
			$alipayData['receipt_amount'] = isset($_POST['receipt_amount']) ? $_POST['receipt_amount'] : '';
			$alipayData['invoice_amount'] = isset($_POST['invoice_amount']) ? $_POST['invoice_amount'] : '';
			$alipayData['buyer_pay_amount'] = isset($_POST['buyer_pay_amount']) ? $_POST['buyer_pay_amount'] : '';
			$alipayData['point_amount'] = isset($_POST['point_amount']) ? $_POST['point_amount'] : '';
			$alipayData['refund_fee'] = isset($_POST['refund_fee']) ? $_POST['refund_fee'] : '';
			$alipayData['subject'] = isset($_POST['subject']) ? $_POST['subject'] : '';
			$alipayData['body'] = isset($_POST['body']) ? $_POST['body'] : '';
			$alipayData['gmt_create'] = isset($_POST['gmt_create']) ? strtotime($_POST['gmt_create']) : '';
			$alipayData['gmt_payment'] = isset($_POST['gmt_payment']) ? strtotime($_POST['gmt_payment']) : '';
			$alipayData['gmt_refund'] = isset($_POST['gmt_refund']) ? $_POST['gmt_refund'] : '';
			$alipayData['gmt_close'] = isset($_POST['gmt_close']) ? $_POST['gmt_close'] : '';
			$alipayData['fund_bill_list'] = isset($_POST['fund_bill_list']) ? $_POST['fund_bill_list'] : '';
			$alipayData['voucher_detail_list'] = isset($_POST['voucher_detail_list']) ? $_POST['voucher_detail_list'] : '';
			$alipayData['passback_params'] = isset($_POST['passback_params']) ? $_POST['passback_params'] : '';
			$id = db('alipay_record')->insertGetId($alipayData);

			if(!$id){
				echo 'fail';
				exit();
			}
			//这里是你的业务逻辑
			echo "success";
		}else{
			//验证失败
			echo "fail";
		}

		}

	}


}