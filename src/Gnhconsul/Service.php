<?php
/**
 * Created by PhpStorm.
 * User: zly
 * Date: 18/2/1
 * Time: 下午5:21
 */

namespace Gnhconsul;

use PurplePixie\PhpDns\DNSQuery;

class Service
{
    private $consulDnsHost = "127.0.0.1:8600";  //默认本地consul接口
    private $type = "SRV";                      //srv模式
    private $ipType = "A";                      //A获取ip模式

    /**
     * 获取consul的域名和端口号
     * @return string
     */
    public function getConsulDnsHost()
    {
        return $this->consulDnsHost;
    }

    /**
     * 获取类型
     * @return string
     */
    public function getIpType()
    {
        return $this->ipType;
    }

    /**
     * 获取类型默认srv
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 设置consul域名和端口
     * @param string $consulDnsHost
     */
    public function setConsulDnsHost($consulDnsHost)
    {
        $this->consulDnsHost = $consulDnsHost;
    }

    /**
     * 设置类型
     * @param string $ipType
     */
    public function setIpType($ipType)
    {
        $this->ipType = $ipType;
    }

    /**
     * 设置类型
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * 获取ip端口列表
     * @param $tag
     * @param $serviceName
     * @param $dc
     * @return array
     */
    public function getService($tag,$serviceName,$dc)
    {
        $dns_query = new DNSQuery($this->consulDnsHost);
        $question = $tag.".".$serviceName.".service.".$dc.".consul.";
        $result= $dns_query->Query($question, $this->type);
        $arr = array();
        if ($result && $result->count()>0)
        {
            foreach ($result as $dnsResult)
            {
                if ($dnsResult->getTypeid() == $this->type)
                {
                    $extras = $dnsResult->getExtras();
                    $extras['hostname'] = $dnsResult->getData();
                    $arr[] = $extras;
                }
            }
        }


        foreach ($arr as $key => $value)
        {
            $result= $dns_query->Query($value['hostname'], $this->ipType);
            if ( $result && $result->count()>0)
            {
                foreach ($result as $dnsResult)
                {
                    if ($dnsResult->getTypeid() == $this->ipType)
                    {
                        $arr[$key]['ip'] = $dnsResult->getData();
                    }
                }
            }
        }

        //domain
        $ipList = array();
        foreach ($arr as $value)
        {
            $ipList[] = $value['ip'].':'.$value['port'];
        }

        return $ipList;

    }
}
