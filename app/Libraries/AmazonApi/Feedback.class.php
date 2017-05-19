<?php

class Feedback
{
    public $service;
    protected $sleepTime;
    protected $curTime;
    protected $userToken;

    public function __construct($tokenkey)
    {
        if(file_exists(storage_path('keys/amazon/') . "amazon_keys_{$tokenkey}.php")){
            $userToken = require_once(storage_path('keys/amazon/') . "amazon_keys_{$tokenkey}.php");
            $this->userToken = $userToken;
        }else{
            throw new InvalidArgumentException("The token file does not exist");
        }

        // 加载Amazon WebServices
        require_once app_path('Libraries/AmazonApi/') . 'MarketplaceWebService/Samples/.config.inc.php';

        $config  = array(
            'ServiceURL'    => $userToken['SERVER_URL'],
            'ProxyHost'     => null,
            'ProxyPort'     => -1,
            'MaxErrorRetry' => 3,
        );

        $this->service = new \MarketplaceWebService_Client(AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, $config, APPLICATION_NAME, APPLICATION_VERSION);
    }

    public function init($sleepTime = 100, $curTime)
    {
        $this->sleepTime = $sleepTime;
        $this->curTime = $curTime;
    }

    /**
     *  执行请求获取报表的时间范围
     *  @param MarketplaceWebService_Interface $service
     */
    public function invokeGetRequestReport(MarketplaceWebService_Interface $service, $dayTime)
    {
        $request = new MarketplaceWebService_Model_RequestReportRequest();
        $marketplaceIdArray = array(
            "Id" => array(
                MARKETPLACE_ID,
            ),
        );

        $request->setMarketplaceIdList($marketplaceIdArray);
        $request->setMerchant(MERCHANT_ID);
        $request->setReportType('_GET_SELLER_FEEDBACK_DATA_');

        //限定时间范围
        $request->setStartDate(new DateTime("-{$dayTime} day", new DateTimeZone('UTC')));
        $request->setEndDate(new DateTime("+1 day", new DateTimeZone('UTC')));

        // Using ReportOptions
        // $request->setReportOptions('ShowSalesChannel=true');

        return $this->invokeRequestReport($service, $request);
    }

    /**
     * 提交获取报告请求时间范围
     * @param MarketplaceWebService_Interface $service
     * @param unknown_type $request
     */
    public function invokeRequestReport(MarketplaceWebService_Interface $service, $request)
    {
        try {
            $response = $service->requestReport($request);

            //echo ("Service Response\n");
            //echo ("=============================================================================\n");

            //echo("        RequestReportResponse\n");
            if ($response->isSetRequestReportResult()) {
                //echo("            RequestReportResult\n");
                $requestReportResult = $response->getRequestReportResult();

                if ($requestReportResult->isSetReportRequestInfo()) {

                    $reportRequestInfo = $requestReportResult->getReportRequestInfo();
                    //echo("                ReportRequestInfo\n");
                    if ($reportRequestInfo->isSetReportRequestId()) {
                        //echo("                    ReportRequestId\n");
                        //echo("                        " . $reportRequestInfo->getReportRequestId() . "\n");
                    }
                    if ($reportRequestInfo->isSetReportType()) {
                        //echo("                    ReportType\n");
                        //echo("                        " . $reportRequestInfo->getReportType() . "\n");
                    }
                    if ($reportRequestInfo->isSetStartDate()) {
                        //echo("                    StartDate\n");
                        //echo("                        " . $reportRequestInfo->getStartDate()->format(DATE_FORMAT) . "\n");
                    }
                    if ($reportRequestInfo->isSetEndDate()) {
                        //echo("                    EndDate\n");
                        //echo("                        " . $reportRequestInfo->getEndDate()->format(DATE_FORMAT) . "\n");
                    }
                    if ($reportRequestInfo->isSetSubmittedDate()) {
                        //echo("                    SubmittedDate\n");
                        //echo("                        " . $reportRequestInfo->getSubmittedDate()->format(DATE_FORMAT) . "\n");
                    }
                    if ($reportRequestInfo->isSetReportProcessingStatus()) {
                        //echo("                    ReportProcessingStatus\n");
                        //echo("                        " . $reportRequestInfo->getReportProcessingStatus() . "\n");
                    }
                }
            }
            if ($response->isSetResponseMetadata()) {
                //echo("            ResponseMetadata\n");
                $responseMetadata = $response->getResponseMetadata();
                if ($responseMetadata->isSetRequestId()) {
                    //echo("                RequestId\n");
                    //echo("                    " . $responseMetadata->getRequestId() . "\n");
                }
            }

            return array(
                'ReportProcessingStatus' => $reportRequestInfo->getReportProcessingStatus(),
                'ReportRequestId'        => $reportRequestInfo->getReportRequestId(),
                'ReportType'             => $reportRequestInfo->getReportType(),
                'StartDate'              => $reportRequestInfo->getStartDate()->format(DATE_FORMAT),
                'EndDate'                => $reportRequestInfo->getEndDate()->format(DATE_FORMAT),
                'SubmittedDate'          => $reportRequestInfo->getSubmittedDate()->format(DATE_FORMAT),
            );
            //echo("            ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");
        }catch(MarketplaceWebService_Exception $ex){
            echo("Caught Exception: ".$ex->getMessage()."\n");
            echo("Response Status Code: ".$ex->getStatusCode()."\n");
            echo("Error Code: ".$ex->getErrorCode()."\n");
            echo("Error Type: ".$ex->getErrorType()."\n");
            echo("Request ID: ".$ex->getRequestId()."\n");
            echo("XML: ".$ex->getXML()."\n");
            echo("ResponseHeaderMetadata: ".$ex->getResponseHeaderMetadata()."\n");
        }
    }

    /**
     *  拉取评价请求
     */
    public function fetchFeedbackRequest($reportRequestInfo = array(), $hoursTime)
    {
        $request = new \MarketplaceWebService_Model_GetReportRequestListRequest();
        $request->setMarketplace(MARKETPLACE_ID);
        $request->setMerchant(MERCHANT_ID);
        $request->setReportTypeList(array(
            "Type" => array(
                '_GET_SELLER_FEEDBACK_DATA_',
            ),
        ));
        if(!empty($reportRequestInfo['ReportRequestId'])){
            $request->setReportRequestIdList(array(
                "Id" => array(
                    $reportRequestInfo['ReportRequestId'],
                ),
            ));
        }

        //限定时间范围,reportRequestId不存在则取列表中前五十个
        $request->setRequestedFromDate(new DateTime("-{$hoursTime}".' hour', new DateTimeZone('UTC')));
        $request->setRequestedToDate(new DateTime('now', new DateTimeZone('UTC')));
        $request->setMaxCount(50);

        $nextTokenReturn = $this->invokeGetReportRequestList($this->service, $request);
    }

    /**
     * 通过API获取生成的报告ID列表
     * @param MarketplaceWebService_Interface $service
     * @param object $request
     */
    protected function invokeGetReportRequestList(MarketplaceWebService_Interface $service, $request){
        try{
            $response = $service->getReportRequestList($request);
            dump($response);
            if($response->isSetGetReportRequestListResult()){
                $getReportRequestListResult = $response->getGetReportRequestListResult();
                if($getReportRequestListResult->isSetNextToken()){

                    $nextToken = $getReportRequestListResult->getNextToken();
                }
                $reportRequestInfoList = $getReportRequestListResult->getReportRequestInfoList();

                foreach($reportRequestInfoList as $reportRequestInfo){
                    if($reportRequestInfo->getReportType() == '_GET_SELLER_FEEDBACK_DATA_'){
                        if($reportRequestInfo->getReportProcessingStatus() == '_DONE_'){
                            $generatedReportId = $reportRequestInfo->getGeneratedReportId();
                            if(!empty($generatedReportId)){
                                echo "--------[date:{$this->curTime}][{$this->tokenKey}]Current Generateid:[{$generatedReportId}] Start getting comments--------------\n";
                                $requestReportObj = new \MarketplaceWebService_Model_GetReportRequest();
                                $requestReportObj->setMarketplace(MARKETPLACE_ID);
                                $requestReportObj->setMerchant(MERCHANT_ID);
                                $requestReportObj->setReport(@fopen('php://memory', 'rw+'));
                                $requestReportObj->setReportId($generatedReportId);
                                echo "--------We need to delay {$this->sleepTime} seconds before initiation request-----\n";
                                sleep($this->sleepTime);
                                
                                //获取评论，入库
                                //$this->invokeGetReport($service, $requestReportObj);
                                // $reportRequestInfo->getStartDate()->format(DATE_FORMAT);
                            }else{
                                echo "---------[date:{$curTime}][{$this->tokenKey}]No Generateid ----------------\n";
                            }
                        }
                    }
                }
            }

            if($response->isSetResponseMetadata()){
                // echo(" ResponseMetadata\n");
                $responseMetadata = $response->getResponseMetadata();
                if($responseMetadata->isSetRequestId()){
                    // echo(" RequestId\n");
                    // echo(" " . $responseMetadata->getRequestId() . "\n");
                }
            }

            return !empty($nextToken) ? $nextToken : '';

        }catch(MarketplaceWebService_Exception $ex){
            echo("Caught Exception List: ".$ex->getMessage()."\n");
            echo("Response Status Code: ".$ex->getStatusCode()."\n");
            echo("Error Code: ".$ex->getErrorCode()."\n");
            echo("Error Type: ".$ex->getErrorType()."\n");
            echo("Request ID: ".$ex->getRequestId()."\n");
            echo("XML: ".$ex->getXML()."\n");
            echo("ResponseHeaderMetadata: ".$ex->getResponseHeaderMetadata()."\n");
        }
    }

    /**
     * 根据生成的报告ID获取评论信息,并执行入库操作
     * @param MarketplaceWebService_Interface $service
     * @param object $request
     * @return Ambigous <string, multitype:, unknown>
     */
    protected function invokeGetReport(MarketplaceWebService_Interface $service, $request){
        return false;
        //global $db, $userToken, $curTime, $tokenKey, $sleepOrderTime, $aryMarketId;
        try{
            $response = $service->getReport($request);
            // echo ("Service Response\n");
            // echo
            // ("=============================================================================\n");
            // echo(" GetReportResponse\n");
            if($response->isSetGetReportResult()){
                $getReportResult = $response->getGetReportResult();
                // echo (" GetReport");

                if($getReportResult->isSetContentMd5()){
                    // echo (" ContentMd5");
                    // echo (" " . $getReportResult->getContentMd5() . "\n");
                }
            }
            if($response->isSetResponseMetadata()){
                // echo(" ResponseMetadata\n");
                $responseMetadata = $response->getResponseMetadata();
                if($responseMetadata->isSetRequestId()){
                    // echo(" RequestId\n");
                    // echo(" " . $responseMetadata->getRequestId() . "\n");
                }
            }
            // echo (" Report Contents\n");
            // echo (stream_get_contents($request->getReport()) . "\n");
            // 解析评价内容
            $reportContents = stream_get_contents($request->getReport());
            // echo 'run already';
            //print_r ( $reportContents );
            if(!empty($reportContents)){
                echo "------------------------[date:{$curTime}][{$tokenKey}]Start Fetch Content--------------------------------------\n";
                $aryReportContents = explode("\n", $reportContents);
                return '1';
                if(!empty($aryReportContents)){
                    // echo 'run already';
                    foreach($aryReportContents as $key => $item){
                        if($key > 0){
                            if(!empty($item)){
                                //解析拉取的评论数据
                                $aryItem = preg_split("/\t+/", $item);
                                //print_r ( $aryItem );
                                //////////////////////////////////////////////////拉取订单信息/////////////////////////////////////////////
                                if(!empty($aryItem[7])){
                                    $config  = array(
                                        'ServiceURL'    => $userToken['SERVER_ORDER_URL'],
                                        'ProxyHost'     => null,
                                        'ProxyPort'     => -1,
                                        'MaxErrorRetry' => 3,
                                    );
                                    $service = new \MarketplaceWebServiceOrders_Client(AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, APPLICATION_NAME, APPLICATION_VERSION, $config);
                                    $request = new \MarketplaceWebServiceOrders_Model_ListOrderItemsRequest();
                                    $request->setSellerId(MERCHANT_ID);
                                    $request->setAmazonOrderId($aryItem[7]);


                                    $aryRtnOrder['email'] = $aryItem[8];
                                    /***************************通过接口获取账号及运输方式***************************************/
                                    $aryFetchOrder = UserCacheModel::getOrderInfoByOrderId($aryItem[7]);
                                    if(!empty($aryFetchOrder['data']['ebay_carrier'])){

                                        $aryRtnOrder['carrytype'] = $aryFetchOrder['data']['ebay_carrier'];
                                    }else{

                                        $aryNewFetchOrder         = UserCacheModel::getNewOrderInfoByOrderId($aryItem[7]);
                                        $aryRtnOrder['carrytype'] = current($aryNewFetchOrder['data']);
                                    }

                                    if($aryFetchOrder['res_code'] == '200'){
                                        //$aryRtnOrder['commentuser']             = $aryFetchOrder['data']['ebay_userid'];
                                        $aryRtnOrder['carrytype'] = $aryFetchOrder['data']['ebay_carrier'];
                                    }

                                    /************************************获取订单唯一国家编码*******************************************/
                                    echo "--------We need to delay {$sleepOrderTime} seconds before initiation request-----\n";
                            //                                sleep($sleepOrderTime);
                                    $arySingleOrder               = sendGetOrder($service, $aryItem[7]);
                                    $siteToken = substr(strrchr($userToken['ACCOUNT'], "_"), 1);//站点
                            //                                 $aryAccount = explode("_", $userToken['ACCOUNT']);//站点
                            //                                 if (count($aryAccount) == 3) {
                            //                                  $siteToken    = $aryAccount[2];
                            //                                 } else {
                            //                                  $siteToken    = $aryAccount[1];
                            //                                 }
                                    $aryRtnOrder['commentuser']   = $arySingleOrder['BuyerName'];
                                    $marketplaceId                = $arySingleOrder['MarketplaceId'];
                                    $aryRtnOrder['marketplaceid'] = !empty($marketplaceId) ? $marketplaceId : $aryMarketId[$siteToken];
                                    if (empty($marketplaceId)) {
                                        $errMsg = "----订单号：{$aryItem[7]}--站点:{$siteToken}--国家简码:{$marketplaceId}--写死为:{$aryRtnOrder['marketplaceid']}--\n";
                                        echo $errMsg;
                                        log::write($errMsg,$level,$type,WEB_PATH."log/feedback/".date('Y-m-d').".log");
                                    }
                                    // object or array of parameters
                                    invokeListOrderItems($service, $request, $aryRtnOrder);
                                }
                                
                                $siteToken = substr(strrchr($userToken['ACCOUNT'], "_"), 1);//站点
                                $accountToken   = substr($userToken['ACCOUNT'], 0, -3);
                                echo $siteToken.PHP_EOL;
                                echo $accountToken.PHP_EOL;
                                //                             $aryAccount = explode("_", $userToken['ACCOUNT']);
                                //                             $siteToken    = $aryAccount[count($aryAccount)-1];
                                //                             if (count($aryAccount) == 3) {
                                //                              $accountToken = $aryAccount[0].'_'.$aryAccount[1];
                                //                              $siteToken    = $aryAccount[2];
                                //                             } else {
                                //                              $accountToken = $aryAccount[0];
                                //                              $siteToken    = $aryAccount[1];
                                //                             }
                                // 检索是否重复入库
                                $aryWhere['orderid'] = $aryItem[7];
                                $aryWhere['account'] = $accountToken;//账号
                                $aryWhere['state']   = $siteToken;//地区
                                $aryWhere['isdelete']= '0';
                                if(!$db->count('fb_request_amazon', $aryWhere)){
                                    $aryNewReportContents['commenttime'] = $aryItem[0];
                                    $aryNewReportContents['rating']      = $aryItem[1];
                                    $aryNewReportContents['lastrating']  = $aryItem[1];
                                    //国家判断，转换编码,德语,法国,日本例外
                                    if(stripos($aryItem[8], '.de') !== false){
                                        $aryItem[2] = iconv("ISO-8859-1", "UTF-8", $aryItem[2]);
                                    }
                                    if(stripos($aryItem[8], '.fr') !== false){
                                        $aryItem[2] = iconv("ISO-8859-15", "UTF-8", $aryItem[2]);
                                    }
                                    if(stripos($aryItem[8], '.jp') !== false){
                                        $aryItem[2] = iconv("Shift_JIS", "UTF-8", $aryItem[2]);
                                    }

                                    //日期格式统一转化,美国,德国一致采用 月日年
                                    $aryTime = explode("/", $aryItem[0]);
                                    if(stripos($aryItem[8], ".de") !== false || stripos($aryItem[8], ".com") !== false || stripos($aryItem[8], ".fr") !== false){

                                        //纠正个别错误
                                        if(strnatcmp($aryTime[0], "12") > 0 || strtotime($aryItem[0]) > time()){
                                            $strTime                              = $aryTime[1].'/'.$aryTime[0].'/'.$aryTime[2];
                                            $aryNewReportContents['commentstamp'] = strtotime($strTime);
                                        }else{
                                            $aryNewReportContents['commentstamp'] = strtotime($aryItem[0]);
                                        }

                                        //以点号分割日期处理
                                        if(stripos($aryItem[0], ".") !== false){
                                            $aryPointTime                         = explode(".", $aryItem[0]);
                                            $strPointTime                         = $aryPointTime[1].'/'.$aryPointTime[0].'/'.$aryPointTime[2];
                                            $aryNewReportContents['commentstamp'] = strtotime($strPointTime);
                                        }

                                    }else{
                                        if(stripos($aryItem[8], ".jp")){
                                            if(strnatcmp($aryTime[0], "12") > 0){
                                                $strTime                              = $aryTime[1].'/'.$aryTime[2].'/'.$aryTime[0];
                                                $aryNewReportContents['commentstamp'] = strtotime($strTime);
                                            }else{
                                                $aryNewReportContents['commentstamp'] = strtotime($aryItem[0]);
                                            }
                                        }else{

                                            $strTime                              = $aryTime[1].'/'.$aryTime[0].'/'.$aryTime[2];
                                            $aryNewReportContents['commentstamp'] = strtotime($strTime);

                                        }
                                    }

                                    $aryNewReportContents['comments']      = $aryItem[2];
                                    $aryNewReportContents['response']      = $aryItem[4];
                                    $aryNewReportContents['arrived']       = $aryItem[5];
                                    $aryNewReportContents['service']       = $aryItem[6];
                                    $aryNewReportContents['orderid']       = $aryItem[7];
                                    $aryNewReportContents['email']         = $aryItem[8];
                                    $aryNewReportContents['role']          = $aryItem[9];
                                    $aryNewReportContents['inputtime']     = time();
                                    $aryNewReportContents['updatetime']    = $aryNewReportContents['commentstamp'];
                                    $aryNewReportContents['commentuser']   = $aryRtnOrder['commentuser'];
                                    $aryNewReportContents['marketplaceid'] = $aryRtnOrder['marketplaceid'];
                                    $aryNewReportContents['account']       = $accountToken;
                                    $aryNewReportContents['state']         = $siteToken;

                                    //归属文件夹id 旧规则
                                    if(!empty($aryNewReportContents['commentuser'])){
                                        $strRules                       = mb_substr(trim($aryNewReportContents['commentuser']), 0, 1, "utf-8");
                                        $strAct                         = $aryNewReportContents['account'];
                                        $strState                       = $aryNewReportContents['site'];
                                        $strFolderWhere                 = " platform= 1 AND is_delete=0 AND BINARY rules LIKE '%{$strRules}%' AND accounts LIKE '%{$strAct}%' AND sites LIKE '%{$strState}%' ";
                                        $aryRuleAct                     = $db->getone('id', 'fb_accounts_rules', $strFolderWhere);
                                        $aryNewReportContents['foldid'] = !empty($aryRuleAct['id']) ? $aryRuleAct['id'] : '-1';
                                    }else{
                                        $aryNewReportContents['foldid'] = '-1';
                                    }

                                    //归属文件夹id 新规则
                                    $account    = $aryNewReportContents['account'];
                                    $site       = $aryNewReportContents['state'];
                                    $ordernum   = $aryNewReportContents['orderid'];
                                    $buyerid    = $aryNewReportContents['commentuser'];
                                    $buyeremail = $aryNewReportContents['email'];
                                    //if(!empty($aryNewReportContents['commentuser'])){
                                        $aryNewReportContents['folderid_1'] = UserCacheModel::getFolderId($account, $site, $ordernum, $buyerid, $buyeremail)['data'];
                                    //}else{
                                       // $aryNewReportContents['folderid_1'] = '-1';
                                    //}
                                    //入库评论内容
                                    if($db->insert($aryNewReportContents, 'fb_request_amazon')){

                                        //判断首字母规则是否存在，不存在则添加
                                        if(!empty($aryRtnOrder['commentuser'])){
                                            $firstLetter          = mb_substr($aryRtnOrder['commentuser'], 0, 1, 'utf-8');
                                            $aryLetterMap['rule'] = $firstLetter;
                                            if($db->count("fb_rules", $aryLetterMap) == 0){
                                                $aryInsLetter['rule'] = $firstLetter;
                                                $runSta               = $db->insert($aryInsLetter, 'fb_rules');
                                            }
                                        }

                                        $cateType = $aryNewReportContents['rating'];
                                        if($cateType < 3){
                                            $fbType = 3;
                                        }elseif($cateType == 3){
                                            $fbType = 2;
                                        }elseif($cateType > 3){
                                            $fbType = 1;
                                        }
                                        ////////////////////////更新评价到老订单系统////////////////////////////
                                        UserCacheModel::updateErpOrderInfoFromAmazonFeedback($aryNewReportContents['orderid'], $fbType);
                                        ////////////////////////更新评价到新订单系统////////////////////////////
                                        $aryCallNewRtn = UserCacheModel::updateNewErpOrderInfoFromAmazonFeedback($aryNewReportContents['orderid'], $fbType);
                                        if($aryCallNewRtn['data'] != 'success'){
                                            //记录错误日志到表
                                            $aryLogMap['orderid'] = $aryNewReportContents['orderid'];
                                            if($db->count("fb_sync_log", $aryLogMap) == 0){
                                                $aryIns['orderid']     = $aryNewReportContents['orderid'];
                                                $aryIns['commenttype'] = $fbType;
                                                $db->insert($aryIns, "fb_sync_log");
                                            }
                                        }

                                        echo "--------[date:{$curTime}][{$tokenKey}] orderNumber：{$aryItem[7]}----CommentTime:{$aryItem[0]}-----Ratings:{$aryItem[1]} comments insert success--------\n";

                                    }else{
                                        throw new Exception(mysql_error());
                                    }
                                }else{
                                    echo "--------[date:{$curTime}][{$tokenKey}] orderNumber:{$aryWhere ['orderid']} comments has been inserted --------\n";
                                }
                            }
                        }
                    }
                }else{
                    echo "--------[date:{$curTime}][{$tokenKey}] No Report Contents--------\n";
                }
            }
            // echo(" ResponseHeaderMetadata: " .
            // $response->getResponseHeaderMetadata() . "\n");
        }catch(MarketplaceWebService_Exception $ex){
            echo("Caught Exception Report: ".$ex->getMessage()."\n");
            echo("Response Status Code: ".$ex->getStatusCode()."\n");
            echo("Error Code: ".$ex->getErrorCode()."\n");
            echo("Error Type: ".$ex->getErrorType()."\n");
            echo("Request ID: ".$ex->getRequestId()."\n");
            echo("XML: ".$ex->getXML()."\n");
            echo("ResponseHeaderMetadata: ".$ex->getResponseHeaderMetadata()."\n");
        }
    }
}