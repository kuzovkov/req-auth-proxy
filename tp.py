#!/usr/bin/env python
#coding=utf-8

import urllib2
import re

url="https://2ip.ru"
proxy_address = '62.109.2.4:3129'
proxy_account = 'Buffa4ok:lAs5ok7y'
use_proxy = True

def add_proxy():
    if use_proxy:    
        proxy_handler = urllib2.ProxyHandler({'https': ''.join(['https://',proxy_account,'@',proxy_address])})
        proxy_auth_handler = urllib2.ProxyBasicAuthHandler()
        opener = urllib2.build_opener(proxy_handler, proxy_auth_handler, urllib2.HTTPHandler)
        urllib2.install_opener(opener)

try:
    req = urllib2.Request(url)    
    add_proxy()
    res = urllib2.urlopen(req)
except Exception, ex:
    print "Не могу открыть ",url
    print ex
else:
    re_need_content = re.compile('<big id="d_clip_button".*')
    content = res.read()
    need_content = re_need_content.search(content)    
    if need_content is not None:
        print need_content.group()
    print "-"*60
    
    
