<?php
/**
 * Created by PhpStorm.
 * User: zly
 * Date: 18/2/1
 * Time: 下午5:21
 */

namespace webzly\gnhconsul;

use PurplePixie\PhpDns\DNSQuery;

class gnhconsul
{
    protected $consulDnsHost = "127.0.0.1:8600";
    protected $type = "SRV";
    protected $ipType = "A";


    public function getService($tag,$serviceName,$dc)
    {
        $dns_query = new DNSQuery($this->consulDnsHost);
        $question = $tag.".".$serviceName.".service.".$dc.".consul.";
        $result= $dns_query->Query($question, $this->type);
        $arr = array();
        if ($result->count()>0)
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


        foreach ($arr as &$value)
        {
            $result= $dns_query->Query($value['hostname'], $this->ipType);
            if ($result->count()>0)
            {
                foreach ($result as $dnsResult)
                {
                    if ($dnsResult->getTypeid() == $this->ipType)
                    {
                        $value['ip'] = $dnsResult->getData();
                    }
                }
            }
        }

        return $arr;

    }
}
