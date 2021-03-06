<?php
//
//    ______         ______           __         __         ______
//   /\  ___\       /\  ___\         /\_\       /\_\       /\  __ \
//   \/\  __\       \/\ \____        \/\_\      \/\_\      \/\ \_\ \
//    \/\_____\      \/\_____\     /\_\/\_\      \/\_\      \/\_\ \_\
//     \/_____/       \/_____/     \/__\/_/       \/_/       \/_/ /_/
//
//   上海商创网络科技有限公司
//
//  ---------------------------------------------------------------------------------
//
//   一、协议的许可和权利
//
//    1. 您可以在完全遵守本协议的基础上，将本软件应用于商业用途；
//    2. 您可以在协议规定的约束和限制范围内修改本产品源代码或界面风格以适应您的要求；
//    3. 您拥有使用本产品中的全部内容资料、商品信息及其他信息的所有权，并独立承担与其内容相关的
//       法律义务；
//    4. 获得商业授权之后，您可以将本软件应用于商业用途，自授权时刻起，在技术支持期限内拥有通过
//       指定的方式获得指定范围内的技术支持服务；
//
//   二、协议的约束和限制
//
//    1. 未获商业授权之前，禁止将本软件用于商业用途（包括但不限于企业法人经营的产品、经营性产品
//       以及以盈利为目的或实现盈利产品）；
//    2. 未获商业授权之前，禁止在本产品的整体或在任何部分基础上发展任何派生版本、修改版本或第三
//       方版本用于重新开发；
//    3. 如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回并承担相应法律责任；
//
//   三、有限担保和免责声明
//
//    1. 本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的；
//    2. 用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未获得商业授权之前，我们不承
//       诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任；
//    3. 上海商创网络科技有限公司不对使用本产品构建的商城中的内容信息承担责任，但在不侵犯用户隐
//       私信息的前提下，保留以任何方式获取用户信息及商品信息的权利；
//
//   有关本产品最终用户授权协议、商业授权与技术服务的详细内容，均由上海商创网络科技有限公司独家
//   提供。上海商创网络科技有限公司拥有在不事先通知的情况下，修改授权协议的权力，修改后的协议对
//   改变之日起的新授权用户生效。电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和
//   等同的法律效力。您一旦开始修改、安装或使用本产品，即被视为完全理解并接受本协议的各项条款，
//   在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本
//   授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。
//
//  ---------------------------------------------------------------------------------
//
/**
 * 订单查询
 */
defined('IN_ECJIA') or exit('No permission resources.');

RC_Loader::load_app_class('platform_abstract', 'platform', false);
class mp_orders extends platform_abstract
{    

	/**
	 * 获取插件配置信息
	 */
	public function local_config() {
		$config = include(RC_Plugin::plugin_dir_path(__FILE__) . 'config.php');
		if (is_array($config)) {
			return $config;
		}
		return array();
	}
	
    public function event_reply() {

    	$wechatuser_db = RC_Loader::load_app_model('wechat_user_model','wechat');
    	$orders_db = RC_Loader::load_app_model('order_info_model','orders');
    	RC_Loader::load_app_func('admin_order','orders');
    	
    	$openid = $this->from_username;
    	$uid  = $wechatuser_db->where(array('openid' => $openid))->get_field('ect_uid');//获取绑定用户会员id
    	$nobd = "还未绑定，需<a href = '".RC_Uri::url('platform/plugin/show', array('handle' => 'mp_userbind/bind_init', 'openid' => $openid, 'uuid' => $_GET['uuid']))."'>点击此处</a>进行绑定";
    	if(empty($uid)) {
    		$content = array(
				'ToUserName' => $this->from_username,
				'FromUserName' => $this->to_username,
				'CreateTime' => SYS_TIME,
				'MsgType' => 'text',
				'Content' => $nobd
			);
    	} else {
    		$order_id  = $orders_db->where(array('user_id' => $uid))->order('add_time desc')->get_field('order_id');//获取会员当前订单
    		
    		if (!empty($order_id)) {
    			$order	= order_info($order_id);//取得订单信息
    			$order_goods = order_goods($order_id);//去的订单商品简单信息
    			$goods = '';
    			if(!empty($order_goods)){
    				foreach($order_goods as $key=>$val){
    					$goods .= $val['goods_name'].'('.$val['goods_attr'].')('.$val['goods_number'].'), ';
    				}
    			}
    			// 	作何操作0,未确认, 1已确认; 2已取消; 3无效; 4退货
    			if ($order['order_status'] ==1) {
    				$order_status = '未确认';
    			} elseif($order['order_status'] ==2){
    				$order_status = '未确认';
    			} elseif ($order['order_status'] ==3) {
    				$order_status = '未确认';
    			} elseif ($order['order_status'] ==4) {
    				$order_status = '退货';
    			} else {
    				$order_status = '未确认';
    			}
    			 
    			 
    			//发货状态; 0未发货; 1已发货  2已取消  3备货中
    			if ($order['shipping_status'] ==1) {
    				$shipping_status = '已发货';
    			} elseif($order['shipping_status'] ==2){
    				$shipping_status = '已取消';
    			} elseif ($order['shipping_status'] ==3) {
    				$shipping_status = '备货中';
    			} else {
    				$shipping_status = '未发货';
    			}
    			 
    			//支付状态 0未付款;  1已付款中;  2已付款
    			if ($order['pay_status'] ==1) {
    				$pay_status = '已付款中';
    			} elseif($order['pay_status'] ==2){
    				$pay_status = '已付款';
    			} else {
    				$pay_status = '未付款';
    			}
    			 
    			$articles = array();
    			$articles[0]['Title'] = '订单号：'.$order['order_sn'];
    			$articles[0]['PicUrl'] = '';
    			$articles[0]['Description'] = '商品信息：'. $goods ."\r\n". '总金额：'. $order['total_fee'] ."\r\n". '订单状态：'. $order_status . $shipping_status . $pay_status ."\r\n". '快递公司：'. $order['shipping_name'] ."\r\n". '物流单号：' . $order['invoice_no'];
    			$home_url =  RC_Uri::home_url();
    			if (strpos($home_url, 'sites')) {
    				$url = substr($home_url, 0, strpos($home_url, 'sites'));
    				$articles[0]['Url'] = $url.'sites/m/index.php?m=user&c=user_order&a=order_detail&order_id='.$order_id;
    			} else {
    				$articles[0]['Url'] = $home_url.'/sites/m/index.php?m=user&c=user_order&a=order_detail&order_id='.$order_id;
    			}

    			$count = count($articles);
    			$content = array(
    					'ToUserName'    => $this->from_username,
    					'FromUserName'  => $this->to_username,
    					'CreateTime'    => SYS_TIME,
    					'MsgType'       => 'news',
    					'ArticleCount'	=> $count,
    					'Articles'		=> $articles
    			);
    		} else {
    			$content = array(
    				'ToUserName'    => $this->from_username,
    				'FromUserName'  => $this->to_username,
    				'CreateTime'    => SYS_TIME,
    				'MsgType'       => 'text',
    				'Content'		=> '您当前还未产生任何订单'
    			);
    		}
    	}
    	
    	
        return $content;
    }
    
}

// end