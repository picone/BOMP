<?php
$screenWidget = new CWidget();
//显示过滤器的
$div=new CDiv(null, null,'scrollbar_cntr');
if(hasRequest('linkman'))$div->addStyle('display:none');
$screenWidget->addItem($div);
if (empty($this->data['screens'])) {
	$screenWidget->addPageHeader(_('SCREENS'));
	$screenWidget->addItem(BR());
	$screenWidget->addItem(new CTableInfo(_('No screens found.')));

	$screenBuilder = new CScreenBuilder();
	CScreenBuilder::insertScreenStandardJs(array(
		'timeline' => $screenBuilder->timeline
	));
}else{
	if (!isset($this->data['screens'][$this->data['elementIdentifier']])) {
		// this means id was fetched from profile and this screen does not exist
		// in this case we need to show the first one
		$screen = reset($this->data['screens']);
	}
	else {
		$screen = $this->data['screens'][$this->data['elementIdentifier']];
	}

	// if elementid is used to fetch an element, saving it in profile
	if (!$this->data['use_screen_name']) {
		CProfile::update('web.screens.elementid', $screen['screenid'] , PROFILE_TYPE_ID);
	}
    if(!hasRequest('linkman')){
        // page header   筛选的一栏
        $screenWidget->addPageHeader(_('SCREENS'));
        $screenWidget->addItem(BR());

        //append screens combobox to page header   屏幕表头
        $headerForm = new CForm('get','report_screens.php');
        $headerForm->setName('headerForm');
        $headerForm->addVar('fullscreen', $this->data['fullscreen']);

        $elementsComboBox = new CComboBox('elementid', $screen['screenid'], 'submit()');
        foreach ($this->data['screens'] as $dbScreen) {
            $elementsComboBox->addItem($dbScreen['screenid'],
                htmlspecialchars($dbScreen['name']));
        }
        $headerForm->addItem(array(_('Screens').SPACE, $elementsComboBox));

        if (check_dynamic_items($screen['screenid'], 0)) {
            $pageFilter = new CPageFilter(array(
                'groups' => array(
                    'monitored_hosts' => true,
                    'with_items' => true
                ),
                'hosts' => array(
                    'monitored_hosts' => true,
                    'with_items' => true,
                    'DDFirstLabel' => _('not selected')
                )
            ));
        }
        $screenWidget->addHeader($screen['name'], $headerForm);
    }

	// append screens to widget
	$screenBuilder = new CScreenBuilder(array(
		'screenid' => $screen['screenid'],
		'mode' => SCREEN_MODE_PREVIEW,
		'profileIdx' => 'web.screens',
		'profileIdx2' => $screen['screenid'],
		'groupid' =>0,
		'hostid' =>0,
		'period' => $this->data['period'],
		'stime' => $this->data['stime']
	));
    $screenBuilder->screen['hsize']=1;
    $screenBuilder->screen['vsize']=count($screenBuilder->screen['screenitems']);
    foreach($screenBuilder->screen['screenitems'] as $key=>&$val){
        $val['x']=0;
        $val['y']=$key;
    }
	$screenWidget->addItem($screenBuilder->show());

	CScreenBuilder::insertScreenStandardJs(array(
		'timeline' => $screenBuilder->timeline,
		'profileIdx' => $screenBuilder->profileIdx
	));
	$screenWidget->addItem(BR());
    ?>
    <link href="/public/report.css" rel="stylesheet" type="text/css"/>
    <script src="/public/report.js" type="application/javascript"></script>
    <h1><center>报告屏报告</center></h1>
    <form method="post" target="_blank">
        <input type="hidden" name="print" value="1">
        <table class="table">
            <thead>
            <tr>
                <th colspan="1">生成时间</th>
                <td colspan="2"><?php echo zbx_date2str(DATE_TIME_FORMAT_SECONDS);?></td>
                <th colspan="1">统计时段</th>
                <td colspan="2"><?php echo zbx_date2str(DATE_FORMAT,zbxDateToTime($screenBuilder->timeline['stime'])),'到',zbx_date2str(DATE_FORMAT,zbxDateToTime($screenBuilder->timeline['usertime']));if(!hasRequest('linkman')){?><button type="submit">导出PDF</button><?php }?></td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th><label for="linkman">联系人</label></th>
                <td><input type="text" name="linkman" id="linkman" value="<?php echo utf8_decode(getRequest('linkman'));?>"></td>
                <th><label for="department">相关部门</label></th>
                <td><input type="text" name="department" id="department" value="<?php echo utf8_decode(getRequest('department'));?>"></td>
                <th><label for="telphone">电话</label></th>
                <td><input type="text" name="telphone" id="telphone" value="<?php echo utf8_decode(getRequest('telphone'));?>"></td>
            </tr>
            <tr>
                <th>总体健康评价</th>
                <td colspan="5">
                    <table>
                        <tr>
                            <td style="background-color:#AAFFAA"><input type="radio" name="description" id="strong" value="1"<?php if(getRequest('description')==1||!hasRequest('description')){echo 'checked';}?>><label for="strong">健壮</label></td>
                            <td style="background-color:#DBDBDB"><input type="radio" name="description" id="increase" value="2"<?php if(getRequest('description')==2){echo 'checked';}?>><label for="increase">待提高</label></td>
                            <td style="background-color:#FFF6A5"><input type="radio" name="description" id="unhealthy" value="3"<?php if(getRequest('description')==3){echo 'checked';}?>><label for="unhealthy">不健康</label></td>
                            <td style="background-color:#FF3838"><input type="radio" name="description" id="serious" value="4"<?php if(getRequest('description')==4){echo 'checked';}?>><label for="serious">严重问题</label></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th><label for="introduction">报告说明</label></th>
                <td colspan="5"><?php if(hasRequest('introduction')){?><pre><?php echo utf8_decode(getRequest('introduction'));?></pre><?php }else{?><textarea name="introduction" id="introduction" rows="10" onclick="javascript:ResizeTextarea(this,10);" onkeyup="javascript:ResizeTextarea(this,10);"></textarea><?php }?></td>
            </tr>
            <tr>
                <th><label for="problem">关键问题</label></th>
                <td colspan="5"><?php if(hasRequest('problem')){?><pre><?php echo utf8_decode(getRequest('problem'));?></pre><?php }else{?><textarea name="problem" id="problem" rows="1" onclick="javascript:ResizeTextarea(this,1);" onkeyup="javascript:ResizeTextarea(this,1);"></textarea><?php }?></td>
            </tr>
            </tbody>
        </table>
    </form>
    <?php
}
return $screenWidget;