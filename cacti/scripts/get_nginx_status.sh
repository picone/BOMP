#!/bin/bash
#	Get Nginx status script
#	Author: Zheng Feng
#	Mail: mr.ivory@163.com

TMP=/tmp
URL=nginx_status
rm -rf $TMP/$URL
wget -O $TMP/$URL http://$2/$URL > /dev/null 2>&1
#cat $TMP/$URL

case $1 in
	client)
	nginx_active=`cat $TMP/$URL | head -1 | awk '{print $3}'`
	nginx_reading=`cat $TMP/$URL | tail -1 | awk '{print $2}'`
	nginx_writing=`cat $TMP/$URL | tail -1 | awk '{print $4}'`
	nginx_waiting=`cat $TMP/$URL | tail -1 | awk '{print $6}'`
	echo nginx_active:$nginx_active nginx_reading:$nginx_reading nginx_writing:$nginx_writing nginx_waiting:$nginx_waiting
	;;

	socket)
	nginx_accepts=`cat $TMP/$URL | sed -n "3p" | awk '{print $1}'`
	nginx_handled=`cat $TMP/$URL | sed -n "3p" | awk '{print $2}'`
	nginx_requests=`cat $TMP/$URL | sed -n "3p" | awk '{print $3}'`
	echo nginx_accepts:$nginx_accepts nginx_handled:$nginx_handled nginx_requests:$nginx_requests
	;;

	status)
	nginx_active=`cat $TMP/$URL | head -1 | awk '{print $3}'`
        nginx_reading=`cat $TMP/$URL | tail -1 | awk '{print $2}'`
        nginx_writing=`cat $TMP/$URL | tail -1 | awk '{print $4}'`
        nginx_waiting=`cat $TMP/$URL | tail -1 | awk '{print $6}'`
        nginx_accepts=`cat $TMP/$URL | sed -n "3p" | awk '{print $1}'`
        nginx_handled=`cat $TMP/$URL | sed -n "3p" | awk '{print $2}'`
        nginx_requests=`cat $TMP/$URL | sed -n "3p" | awk '{print $3}'`
	echo nginx_accepts:$nginx_accepts nginx_handled:$nginx_handled nginx_requests:$nginx_requests nginx_active:$nginx_active nginx_reading:$nginx_reading nginx_writing:$nginx_writing nginx_waiting:$nginx_waiting
	;;
esac


rm -rf $TMP/$URL
