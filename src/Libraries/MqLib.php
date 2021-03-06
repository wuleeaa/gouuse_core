<?php
/**
 * 队列基类
 * @author zyb
 */
namespace GouuseCore\Libraries;

use GouuseCore\Helpers\FormHelper;
//以下为阿里消息队列推送
use AliyunMNS\Queue;
use AliyunMNS\Client;
use AliyunMNS\Requests\PublishMessageRequest;
use AliyunMNS\Requests\SendMessageRequest;

class MqLib extends Lib
{
	public $client;
	public function __construct()
	{
		//不同环境读取不懂的配置文件
		$this->client = new Client(env('QUEUE_MNS_ENDPOINT'), env('QUEUE_MNS_ACCESS_KEY'), env('QUEUE_MNS_SECRET_KEY'));
	}
	
	/**
	 * 通过消息订阅方式推送消息
	 * @param array $msg_data 消息内容，以下为数组需要的属性
	 * @param string topic_name 消息主题
	 * @param string message_body 消息内容
	 * @return array 返回数据;
	 */
	public function sendTopic($msg_data)
	{
		//$topic_name = env('QUEUE_TOPIC');
		$message_body = trim(FormHelper::__getData($msg_data, 'message_body'));
		//获取主题名称
		$topic_name = trim(FormHelper::__getData($msg_data, 'topic_name', 'v3-main'));
		try
		{
			$queue = $this->client->getTopicRef($topic_name);
			$push_message_obj = new PublishMessageRequest($message_body);
			$re_push_data = $queue->publishMessage($push_message_obj);
			return [$re_push_data->isSucceed() ? 0 : 1, $re_push_data->getMessageId()];
		}
		catch (MnsException $e)
		{ //失败
			return [1, $e->getMnsErrorCode()];
		}
		
	}
	
	/**
	 * 忘队列中加数据
	 * @param unknown $quen_name
	 */
	public function setMq($quen_name, $value)
	{
		$receiptHandle = NULL;
		try
		{
			//获取队列实例
			$queue = $this->client->getQueueRef($quen_name);
			
			$request = new SendMessageRequest($value);
			
			$res = $queue->sendMessage($request);
			
			return true;
		} catch (MnsException $e)
		{
			return false;
		}
	}
	
	/**
	 * 从队列中取出数据
	 * @param unknown $quen_name
	 */
	public function getMq($quen_name)
	{
		$receiptHandle = NULL;
		try
		{
			//获取队列实例
			$queue = $this->client->getQueueRef($quen_name);
			$this->queue = $queue;
			$res = $queue->receiveMessage(30);
			// 2. 获取ReceiptHandle，这是一个有时效性的Handle，可以用来设置Message的各种属性和删除Message。具体的解释请参考：help.aliyun.com/document_detail/27477.html 页面里的ReceiptHandle
			//$receiptHandle = $res->getReceiptHandle();
			return $res;
		} catch (MnsException $e)
		{
			return false;
		}
	}
	
	
	/**
	 * 从队列中删除数据
	 * @param unknown $receiptHandle
	 */
	public function delMq($receiptHandle)
	{
		try
		{
			$res = $this->queue->deleteMessage($receiptHandle);
		}
		catch (MnsException $e)
		{
			echo "DeleteMessage Failed: " . $e . "\n";
			echo "MNSErrorCode: " . $e->getMnsErrorCode() . "\n";
			return false;
		}
	}
	
	
	
	/**
	 * 获取消息订阅服务器提交过来的数据
	 * @param Request $request Request变量
	 * @return array 返回数据;
	 */
	public function getMessageData($request){
		$data = file_get_contents('php://input');
		$msg_base64_json = json_decode($data, true);
		$msg_data_base64_json = $msg_base64_json["Message"];
		$msg_data_json = base64_decode($msg_data_base64_json);
		return json_decode($msg_data_json, true);
	}
}
