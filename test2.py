#!/usr/bin/env python
#coding=utf-8
#script for proxy server testing

import urllib2
import httplib
import re
from pprint import pprint

url="https://btc-e.nz/api/3/info"
headers = {    # common HTTPS headers
        'Accept': 'application/json',
        'Accept-Charset': 'utf-8',
        'Accept-Encoding': 'identity',
        'Cache-Control': 'no-cache',
        'Connection': 'keep-alive',
        }
proxy1 = {'address': 'https://62.109.2.4:3129', 'account': 'Buffa4ok:lAs5ok7y'}
proxy2 = {'address': 'https://27.218.87.17:8998'}

def add_proxy(proxy):    
    if type(proxy) is not dict:
        raise Exception('parameter type proxy must be dict')
    proxy_address = proxy.get('address', None)
    proxy_account = proxy.get('account', None)
    if proxy_address is None and proxy_account is None:
        return
    protocol = proxy_address.split(':')[0]
    ip_port = proxy_address.split('//')[1]
    if proxy_account is None:
        print str({protocol: ''.join([protocol+'://', ip_port])})         
        proxy_handler = urllib2.ProxyHandler({protocol: ''.join([protocol+'://', ip_port])})
        opener = urllib2.build_opener(proxy_handler, urllib2.HTTPHandler)
    else:
        print str({protocol: ''.join([protocol+'://', proxy_account, '@', ip_port])})        
        proxy_handler = urllib2.ProxyHandler({protocol: ''.join([protocol+'://', proxy_account, '@', ip_port])})
        proxy_auth_handler = urllib2.ProxyBasicAuthHandler()
        opener = urllib2.build_opener(proxy_handler, proxy_auth_handler, urllib2.HTTPHandler)
    
    urllib2.install_opener(opener)


def send_request(url):
    global headers
    host = (url.split('//')[1]).split('/')[0]
    print host
    conn = httplib.HTTPSConnection(host, strict=True, timeout=60)
    method = 'POST'
    body = None
    conn.request(method, url, body=body, headers=headers)
    res = conn.getresponse()
    print res.read()
    
        
send_request(url)
#send_request(url, proxy1)     
#send_request(url, proxy2)
