<?php declare(strict_types=1);

namespace TheNorthMemory\Xml\Tests;

use const DIRECTORY_SEPARATOR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

use function array_map;
use function array_walk;
use function file_get_contents;
use function json_encode;
use function is_string;
use function is_null;
use function error_clear_last;
use function error_get_last;
use function method_exists;

use TheNorthMemory\Xml\Transformer;
use PHPUnit\Framework\TestCase;

class TransformerTest extends TestCase
{
    private const FIXTURES = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

    private function getContents(string $filename): string
    {
        return file_get_contents(self::FIXTURES . $filename) ?: '';
    }

    /**
     * @return array<string,array{string,string[]}>
     */
    public function xmlToArrayDataProvider(): array
    {
        return [
            $f = 'alipay-fuwuchuang-event-follow.xml' => [
                self::getContents($f),
                [
                    'AppId', 'FromUserId', 'FromAlipayUserId', 'CreateTime', 'MsgType', 'EventType', 'ActionParam', 'AgreementId', 'AccountNo', 'UserInfo',
                ],
            ],
            $f = 'alipay-fuwuchuang-event-verifygw.xml' => [
                self::getContents($f),
                [
                    'AppId', 'FromUserId', 'CreateTime', 'MsgType', 'EventType', 'ActionParam', 'AgreementId', 'AccountNo',
                ],
            ],
            $f = 'alipay-openapi-alipay_data_dataservice_bill_downloadurl_query_response.xml' => [
                self::getContents($f),
                [
                    'code', 'msg', 'bill_download_url',
                ],
            ],
            $f = 'alipay-openapi-alipay_fund_auth_order_voucher_create_response.xml' => [
                self::getContents($f),
                [
                    'code', 'msg', 'out_order_no', 'out_request_no', 'code_type', 'code_value', 'code_url',
                ],
            ],
            $f = 'weixin-miniprogram-open_product_receive_coupon.xml' => [
                self::getContents($f),
                [
                    'ToUserName', 'FromUserName', 'CreateTime', 'MsgType', 'Event', 'out_coupon_id', 'request_id',
                ],
            ],
            $f = 'weixin-officialaccount-normal-text.xml' => [
                self::getContents($f),
                [
                    'ToUserName', 'FromUserName', 'CreateTime', 'MsgType', 'Content', 'MsgId', 'MsgDataId', 'Idx',
                ],
            ],
            $f = 'weixin-officialaccount-user_authorize_invoice.xml' => [
                self::getContents($f),
                [
                    'ToUserName', 'FromUserName', 'CreateTime', 'MsgType', 'Event', 'SuccOrderId', 'FailOrderId', 'AuthorizeAppId', 'Source',
                ],
            ],
            $f = 'weixin-open-encrypted.xml' => [
                self::getContents($f),
                [
                    'Encrypt', 'MsgSignature', 'TimeStamp', 'Nonce',
                ],
            ],
            $f = 'weixin-open-user_info_modified.xml' => [
                self::getContents($f),
                [
                    'ToUserName', 'FromUserName', 'CreateTime', 'MsgType', 'Event', 'OpenID', 'AppID', 'RevokeInfo',
                ],
            ],
            $f = 'weixin-pay-notification-paysuccess.xml' => [
                self::getContents($f),
                [
                    'appid', 'attach', 'bank_type', 'fee_type', 'is_subscribe', 'mch_id', 'nonce_str', 'openid',
                    'out_trade_no', 'result_code', 'return_code', 'sign', 'time_end', 'total_fee', 'coupon_fee',
                    'coupon_count', 'coupon_type', 'coupon_id', 'trade_type', 'transaction_id',
                ],
            ],
            $f = 'weixin-pay-notification-refund_req_info.xml' => [
                self::getContents($f),
                [
                    'out_refund_no', 'out_trade_no',
                    'refund_account', 'refund_fee', 'refund_id', 'refund_recv_accout', 'refund_request_source', 'refund_status',
                    'settlement_refund_fee', 'settlement_total_fee', 'success_time', 'total_fee', 'transaction_id',
                ],
            ],
            $f = 'weixin-pay-response-getpublickey.xml' => [
                self::getContents($f),
                [
                    'return_code', 'return_msg', 'result_code', 'mch_id', 'pub_key',
                ],
            ],
            $f = 'weixin-pay-response-sendredpack.xml' => [
                self::getContents($f),
                [
                    'sign', 'mch_billno', 'mch_id', 'wxappid', 'send_name', 're_openid', 'total_amount',
                    'total_num', 'wishing', 'client_ip', 'act_name', 'remark', 'scene_id', 'nonce_str', 'risk_info',
                ],
            ],
        ];
    }

    /**
     * @dataProvider xmlToArrayDataProvider
     * @param string $xmlString
     * @param string[] $keys
     */
    public function testToArray(string $xmlString, array $keys): void
    {
        /** @var string[] $array */
        $array = Transformer::toArray($xmlString);

        self::assertIsArray($array);
        self::assertNotEmpty($array);

        array_map(static function($key) use ($array): void {
            static::assertArrayHasKey($key, $array);
            static::assertIsString($array[$key]);
            static::assertStringNotContainsString('<![CDATA[', $array[$key]);
            static::assertStringNotContainsString(']]>', $array[$key]);
        }, $keys);
    }

    /**
     * @return array<string,array{string,mixed}>
     */
    public function xmlToArraRecursiveDataProvider(): array
    {
        return [
            $f = 'alipay-fuwuchuang-event-response.xml' => [
                self::getContents($f),
                [
                    'response'    => ['assertIsArray', ['success' => ['assertIsString', null]]],
                    'app_cert_sn' => ['assertIsString', null],
                    'sign'        => ['assertIsString', null],
                    'sign_type'   => ['assertIsString', null],
                ],
            ],
            $f = 'alipay-fuwuchuang-message-image.xml' => [
                self::getContents($f),
                [
                    'Image'            => ['assertIsArray', ['MediaId' => ['assertIsString', null], 'Format' => ['assertIsString', null]]],
                    'AppId'            => ['assertIsString', null],
                    'MsgType'          => ['assertIsString', null],
                    'CreateTime'       => ['assertIsString', null],
                    'FromUserId'       => ['assertIsString', null],
                    'FromAlipayUserId' => ['assertIsString', null],
                    'MsgId'            => ['assertIsString', null],
                    'UserInfo'         => ['assertIsString', null],
                ],
            ],
            $f = 'alipay-fuwuchuang-message-text.xml' => [
                self::getContents($f),
                [
                    'Text'             => ['assertIsArray', ['Content' => ['assertIsString', null]]],
                    'AppId'            => ['assertIsString', null],
                    'MsgType'          => ['assertIsString', null],
                    'CreateTime'       => ['assertIsString', null],
                    'FromUserId'       => ['assertIsString', null],
                    'FromAlipayUserId' => ['assertIsString', null],
                    'MsgId'            => ['assertIsString', null],
                    'UserInfo'         => ['assertIsString', null],
                ],
            ],
            $f = 'tencent-cos-bucket.xml' => [
                self::getContents($f),
                [
                    'Name'            => ['assertIsString', null],
                    'Prefix'          => ['assertIsString', null],
                    'KeyMarker'       => ['assertIsString', null],
                    'VersionIdMarker' => ['assertIsString', null],
                    'MaxKeys'         => ['assertIsString', null],
                    'IsTruncated'     => ['assertIsString', null],
                    'Version'         => ['assertIsArray', [
                        ['assertIsArray', [
                            'Key'          => ['assertIsString', null],
                            'VersionId'    => ['assertIsString', null],
                            'IsLatest'     => ['assertIsString', null],
                            'LastModified' => ['assertIsString', null],
                            'ETag'         => ['assertIsString', null],
                            'Size'         => ['assertIsString', null],
                            'StorageClass' => ['assertIsString', null],
                            'Owner'        => ['assertIsArray', [
                                'ID'          => ['assertIsString', null],
                                'DisplayName' => ['assertIsString', null],
                            ]],
                        ]],
                        ['assertIsArray', [
                            'Key'          => ['assertIsString', null],
                            'VersionId'    => ['assertIsString', null],
                            'IsLatest'     => ['assertIsString', null],
                            'LastModified' => ['assertIsString', null],
                            'ETag'         => ['assertIsString', null],
                            'Size'         => ['assertIsString', null],
                            'StorageClass' => ['assertIsString', null],
                            'Owner'        => ['assertIsArray', [
                                'ID'          => ['assertIsString', null],
                                'DisplayName' => ['assertIsString', null],
                            ]],
                        ]],
                        ['assertIsArray', [
                            'Key'          => ['assertIsString', null],
                            'VersionId'    => ['assertIsString', null],
                            'IsLatest'     => ['assertIsString', null],
                            'LastModified' => ['assertIsString', null],
                            'ETag'         => ['assertIsString', null],
                            'Size'         => ['assertIsString', null],
                            'StorageClass' => ['assertIsString', null],
                            'StorageTier'  => ['assertIsString', null],
                            'Owner'        => ['assertIsArray', [
                                'ID'          => ['assertIsString', null],
                                'DisplayName' => ['assertIsString', null],
                            ]],
                        ]],
                        ['assertIsArray', [
                            'Key'          => ['assertIsString', null],
                            'VersionId'    => ['assertIsString', null],
                            'IsLatest'     => ['assertIsString', null],
                            'LastModified' => ['assertIsString', null],
                            'ETag'         => ['assertIsString', null],
                            'Size'         => ['assertIsString', null],
                            'StorageClass' => ['assertIsString', null],
                            'Owner'        => ['assertIsArray', [
                                'ID'          => ['assertIsString', null],
                                'DisplayName' => ['assertIsString', null],
                            ]],
                        ]],
                    ]],
                    'DeleteMarker'    => ['assertIsArray', [
                        'Key'          => ['assertIsString', null],
                        'VersionId'    => ['assertIsString', null],
                        'IsLatest'     => ['assertIsString', null],
                        'LastModified' => ['assertIsString', null],
                        'Owner'        => ['assertIsArray', [
                            'ID'          => ['assertIsString', null],
                            'DisplayName' => ['assertIsString', null],
                        ]],
                    ]],
                ],
            ],
            $f = 'tencent-cos-service.xml' => [
                self::getContents($f),
                [
                    'Owner' => ['assertIsArray', [
                        'ID'          => ['assertIsString', null],
                        'DisplayName' => ['assertIsString', null],
                    ]],
                    'Buckets' => ['assertIsArray', [
                        'Bucket' => ['assertIsArray', [
                            ['assertIsArray', [
                                'Name'         => ['assertIsString', null],
                                'Location'     => ['assertIsString', null],
                                'CreationDate' => ['assertIsString', null],
                            ]],
                            ['assertIsArray', [
                                'Name'         => ['assertIsString', null],
                                'Location'     => ['assertIsString', null],
                                'CreationDate' => ['assertIsString', null],
                            ]],
                            ['assertIsArray', [
                                'Name'         => ['assertIsString', null],
                                'Location'     => ['assertIsString', null],
                                'CreationDate' => ['assertIsString', null],
                            ]],
                            ['assertIsArray', [
                                'Name'         => ['assertIsString', null],
                                'Location'     => ['assertIsString', null],
                                'CreationDate' => ['assertIsString', null],
                            ]],
                        ]],
                    ]],
                ],
            ],
            $f = 'tencent-mmp-notification-jobdetail.xml' => [
                self::getContents($f),
                [
                    'JobsDetail' => ['assertIsArray', [
                        'Code'         => ['assertIsString', null],
                        'CreationTime' => ['assertIsString', null],
                        'EndTime'      => ['assertIsString', null],
                        'Input'        => ['assertIsArray', [
                            'CosHeaders' => ['assertIsArray', [
                                ['assertIsArray', [
                                    'Key'   => ['assertIsString', null],
                                    'Value' => ['assertIsString', null],
                                ]],
                                ['assertIsArray', [
                                    'Key'   => ['assertIsString', null],
                                    'Value' => ['assertIsString', null],
                                ]],
                                ['assertIsArray', [
                                    'Key'   => ['assertIsString', null],
                                    'Value' => ['assertIsString', null],
                                ]],
                            ]],
                            'Object'     => ['assertIsString', null],
                            'Region'     => ['assertIsString', null],
                            'BucketId'   => ['assertIsString', null],
                        ]],
                        'JobId'     => ['assertIsString', null],
                        'Message'   => ['assertIsString', null],
                        'Operation' => ['assertIsArray', [
                            'MediaResult' => ['assertIsArray', [
                                'OutputFile' => ['assertIsArray', [
                                    'Bucket'       => ['assertIsString', null],
                                    'ObjectName'   => ['assertIsString', null],
                                    'ObjectPrefix' => ['assertIsString', null],
                                    'Region'       => ['assertIsString', null],
                                ]],
                            ]],
                            'Output' => ['assertIsArray', [
                                'Bucket' => ['assertIsString', null],
                                'Object' => ['assertIsString', null],
                                'Region' => ['assertIsString', null],
                            ]],
                            'TemplateId' => ['assertIsString', null],
                            'TemplateName' => ['assertIsString', null],
                        ]],
                        'Workflow' => ['assertIsArray', [
                            'RunId'        => ['assertIsString', null],
                            'WorkflowId'   => ['assertIsString', null],
                            'WorkflowName' => ['assertIsString', null],
                            'Name'         => ['assertIsString', null],
                        ]],
                        'QueueId' => ['assertIsString', null],
                        'State'   => ['assertIsString', null],
                        'Tag'     => ['assertIsString', null],
                    ]],
                ],
            ],
            $f = 'tencent-mmp-notification-workflowexecution.xml' => [
                self::getContents($f),
                [
                    'WorkflowExecution' => ['assertIsArray', [
                        'RunId' => ['assertIsString', null],
                        'BucketId' => ['assertIsString', null],
                        'Object' => ['assertIsString', null],
                        'CosHeaders' => ['assertIsArray', [
                            'Key'   => ['assertIsString', null],
                            'Value' => ['assertIsString', null],
                        ]],
                        'WorkflowId' => ['assertIsString', null],
                        'WorkflowName' => ['assertIsString', null],
                        'CreateTime' => ['assertIsString', null],
                        'State' => ['assertIsString', null],
                        'Tasks' => ['assertIsArray', [
                            ['assertIsArray', [
                                'Type'         => ['assertIsString', null],
                                'CreateTime'   => ['assertIsString', null],
                                'EndTime'      => ['assertIsString', null],
                                'State'        => ['assertIsString', null],
                                'JobId'        => ['assertIsString', null],
                                'Name'         => ['assertIsString', null],
                                'TemplateId'   => ['assertIsString', null],
                                'TemplateName' => ['assertIsString', null],
                            ]],
                            ['assertIsArray', [
                                'Type'         => ['assertIsString', null],
                                'CreateTime'   => ['assertIsString', null],
                                'EndTime'      => ['assertIsString', null],
                                'State'        => ['assertIsString', null],
                                'JobId'        => ['assertIsString', null],
                                'Name'         => ['assertIsString', null],
                                'TemplateId'   => ['assertIsString', null],
                                'TemplateName' => ['assertIsString', null],
                            ]],
                        ]],
                    ]],
                ],
            ],
            $f = 'weixin-miniprogram-open_product_scene_group_audit.xml' => [
                self::getContents($f),
                [
                    'ToUserName'      => ['assertIsString', null],
                    'FromUserName'    => ['assertIsString', null],
                    'CreateTime'      => ['assertIsString', null],
                    'MsgType'         => ['assertIsString', null],
                    'Event'           => ['assertIsString', null],
                    'SceneGroupAudit' => ['assertIsArray', [
                        'group_id'             => ['assertIsString', null],
                        'reason'               => ['assertIsString', null],
                        'name'                 => ['assertIsString', null],
                        'scene_group_ext_list' => ['assertIsArray', [
                            ['assertIsArray', [
                                'ext_id' => ['assertIsString', null],
                                'status' => ['assertIsString', null],
                                'name'   => ['assertIsString', null],
                            ]],
                            ['assertIsArray', [
                                'ext_id' => ['assertIsString', null],
                                'status' => ['assertIsString', null],
                                'name'   => ['assertIsString', null],
                            ]],
                        ]],
                    ]],
                ],
            ],
            $f = 'weixin-officialaccount-normal-news.xml' => [
                self::getContents($f),
                [
                    'ToUserName'   => ['assertIsString', null],
                    'FromUserName' => ['assertIsString', null],
                    'CreateTime'   => ['assertIsString', null],
                    'MsgType'      => ['assertIsString', null],
                    'ArticleCount' => ['assertIsString', null],
                    'Articles'     => ['assertIsArray', [
                        'item' => ['assertIsArray', [
                            'Title'       => ['assertIsString', null],
                            'Description' => ['assertIsString', null],
                            'PicUrl'      => ['assertIsString', null],
                            'Url'         => ['assertIsString', null],
                        ]],
                    ]],
                ],
            ],
            $f = 'weixin-officialaccount-subscribe_msg_change_event.xml' => [
                self::getContents($f),
                [
                    'ToUserName'              => ['assertIsString', null],
                    'FromUserName'            => ['assertIsString', null],
                    'CreateTime'              => ['assertIsString', null],
                    'MsgType'                 => ['assertIsString', null],
                    'Event'                   => ['assertIsString', null],
                    'SubscribeMsgChangeEvent' => ['assertIsArray', [
                        'List' => ['assertIsArray', [
                            'TemplateId'            => ['assertIsString', null],
                            'SubscribeStatusString' => ['assertIsString', null],
                        ]],
                    ]],
                ],
            ],
            $f = 'weixin-work-create_user.xml' => [
                self::getContents($f),
                [
                    'ToUserName'     => [ 'assertIsString', null],
                    'FromUserName'   => ['assertIsString', null],
                    'CreateTime'     => ['assertIsString', null],
                    'MsgType'        => ['assertIsString', null],
                    'Event'          => ['assertIsString', null],
                    'ChangeType'     => ['assertIsString', null],
                    'UserID'         => ['assertIsString', null],
                    'Name'           => ['assertIsString', null],
                    'Department'     => ['assertIsString', null],
                    'MainDepartment' => ['assertIsString', null],
                    'IsLeaderInDept' => ['assertIsString', null],
                    'DirectLeader'   => ['assertIsString', null],
                    'Position'       => ['assertIsString', null],
                    'Mobile'         => ['assertIsString', null],
                    'Gender'         => ['assertIsString', null],
                    'Email'          => ['assertIsString', null],
                    'BizMail'        => ['assertIsString', null],
                    'Status'         => ['assertIsString', null],
                    'Avatar'         => ['assertIsString', null],
                    'Alias'          => ['assertIsString', null],
                    'Telephone'      => ['assertIsString', null],
                    'Address'        => ['assertIsString', null],
                    'ExtAttr'        => ['assertIsArray', [
                        'Item' => ['assertIsArray', [
                            ['assertIsArray', [
                                'Name' => ['assertIsString', null],
                                'Type' => ['assertIsString', null],
                                'Text' => ['assertIsArray', [
                                    'Value' => ['assertIsString', null],
                                ]],
                            ]],
                            ['assertIsArray', [
                                'Name' => ['assertIsString', null],
                                'Type' => ['assertIsString', null],
                                'Web'  => ['assertIsArray', [
                                    'Title' => ['assertIsString', null],
                                    'Url'   => ['assertIsString', null],
                                ]],
                            ]],
                        ]],
                    ]],
                ],
            ],
            $f = 'weixin-work-sys_approval_change.xml' => [
                self::getContents($f),
                [
                    'ToUserName'   => ['assertIsString', null],
                    'FromUserName' => ['assertIsString', null],
                    'CreateTime'   => ['assertIsString', null],
                    'MsgType'      => ['assertIsString', null],
                    'Event'        => ['assertIsString', null],
                    'AgentID'      => ['assertIsString', null],
                    'ApprovalInfo' => ['assertIsArray', [
                        'SpNo'       => ['assertIsString', null],
                        'SpName'     => ['assertIsString', null],
                        'SpStatus'   => ['assertIsString', null],
                        'TemplateId' => ['assertIsString', null],
                        'ApplyTime'  => ['assertIsString', null],
                        'Applyer'    => ['assertIsArray', [
                            'UserId' => ['assertIsString', null],
                            'Party'  => ['assertIsString', null],
                        ]],
                        'SpRecord' => ['assertIsArray', [
                            ['assertIsArray', [
                                'SpStatus'     => ['assertIsString', null],
                                'ApproverAttr' => ['assertIsString', null],
                                'Details'      => ['assertIsArray', [
                                    ['assertIsArray', [
                                        'Approver' => ['assertIsArray', [
                                            'UserId' => ['assertIsString', null],
                                        ]],
                                        'Speech'   => ['assertIsString', null],
                                        'SpStatus' => ['assertIsString', null],
                                        'SpTime'   => ['assertIsString', null],
                                    ]],
                                    ['assertIsArray', [
                                        'Approver' => ['assertIsArray', [
                                            'UserId' => ['assertIsString', null],
                                        ]],
                                        'Speech'   => ['assertIsString', null],
                                        'SpStatus' => ['assertIsString', null],
                                        'SpTime'   => ['assertIsString', null],
                                    ]],
                                ]],
                            ]],
                            ['assertIsArray', [
                                'SpStatus'     => ['assertIsString', null],
                                'ApproverAttr' => ['assertIsString', null],
                                'Details'      => ['assertIsArray', [
                                    'Approver' => ['assertIsArray', [
                                        'UserId' => ['assertIsString', null],
                                    ]],
                                    'Speech'   => ['assertIsString', null],
                                    'SpStatus' => ['assertIsString', null],
                                    'SpTime'   => ['assertIsString', null],
                                ]],
                            ]],
                        ]],
                        'Notifyer' => ['assertIsArray', [
                            'UserId' => ['assertIsString', null],
                        ]],
                        'Comments' => ['assertIsArray', [
                            'CommentUserInfo' => ['assertIsArray', [
                                'UserId' => ['assertIsString', null],
                            ]],
                            'CommentTime'     => ['assertIsString', null],
                            'CommentContent'  => ['assertIsString', null],
                            'CommentId'       => ['assertIsString', null],
                        ]],
                        'StatuChangeEvent' => ['assertIsString', null],
                    ]],
                ],
            ],
        ];
    }

    /**
     * @param array{string,?array{mixed}} $def
     * @param string $key
     * @param array{string,mixed} $src
     */
    private function depthsAssertion(array $def, string $key, array $src): void
    {
        [$method, $value] = $def;
        self::assertIsArray($src);
        self::assertArrayHasKey($key, $src);
        self::$method($src[$key]);
        if (is_null($value)) {
            self::assertStringNotContainsString('<![CDATA[', $src[$key]);
            self::assertStringNotContainsString(']]>', $src[$key]);
        } else {
            self::assertIsArray($src[$key]);
            array_walk($value, [$this, 'depthsAssertion'], $src[$key]);
        }
    }

    /**
     * @dataProvider xmlToArraRecursiveDataProvider
     * @param string $xmlString
     * @param array{string,mixed} $keys
     */
    public function testToArrayRecursive(string $xmlString, array $keys): void
    {
        /** @var string[] $array */
        $array = Transformer::toArray($xmlString);

        self::assertIsArray($array);
        self::assertNotEmpty($array);

        array_walk($keys, [$this, 'depthsAssertion'], $array);
    }

    /**
     * @return array<string,array{string,?string}>
     */
    public function xmlToArrayBadPhrasesDataProvider(): array
    {
        return [
            $f = '-invalid-fragment_injection.xml' => [self::getContents($f), null],
            $f = '-invalid-xxe_injection.xml'      => [self::getContents($f), null],
            $f = '-invalid-bad_entity.xml'         => [self::getContents($f), '#^Parsing the \$xml failed with the last error#'],
            $f = '-invalid-normal_404.html'        => [self::getContents($f), '#^Parsing the \$xml failed with the last error#'],
        ];
    }

    /**
     * @dataProvider xmlToArrayBadPhrasesDataProvider
     * @param string $xmlString
     * @param ?string $pattern
     */
    public function testToArrayBadPhrases(string $xmlString, ?string $pattern = null): void
    {
        error_clear_last();
        $array = Transformer::toArray($xmlString);
        self::assertIsArray($array);
        if (is_string($pattern)) {
            self::assertEmpty($array);
            /** @var array{'message':string,'type':int,'file':string,'line':int} $err */
            $err = error_get_last();
            if (method_exists($this, 'assertMatchesRegularExpression')) {
                $this->assertMatchesRegularExpression($pattern, $err['message']);
            } else {
                self::assertRegExp($pattern, $err['message']);
            }
        } else {
            self::assertNotEmpty($array);
        }
    }

    /**
     * @return array<string,array{array<mixed>,bool,bool,string,string}>
     */
    public function arrayToXmlDataProvider(): array
    {
        $jsonModifier = JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE;

        return [
            'normal 1-depth array with extra default options' => [
                [
                    'appid' => 'wx2421b1c4370ec43b',
                    'body' => 'dummybot',
                    'mch_id' => '10000100',
                    'detail' => json_encode([['goods_detail' => '华为手机', 'url' => 'https://huawei.com']], $jsonModifier) ?: ''
                ],
                true, false, 'xml', 'item',
            ],
            'normal 1-depth array with headless=false and indent=true' => [
                [
                    'appid' => 'wx2421b1c4370ec43b',
                    'body' => 'dummybot',
                    'mch_id' => '10000100',
                    'detail' => json_encode([['goods_detail' => '华为手机', 'url' => 'https://huawei.com']], $jsonModifier) ?: ''
                ],
                false, true, 'xml', 'item',
            ],
            '2-depth array with extra default options' => [
                [
                    'appid' => 'wx2421b1c4370ec43b',
                    'body' => 'dummybot',
                    'mch_id' => '10000100',
                    'detail' => [['goods_detail' => '华为手机', 'url' => 'https://huawei.com']],
                ],
                true, false, 'xml', 'item',
            ],
            '2-depth array with with headless=false, indent=true and root=qqpay' => [
                [
                    'appid' => 'wx2421b1c4370ec43b',
                    'body' => 'dummybot',
                    'mch_id' => '10000100',
                    'detail' => [['goods_detail' => '华为手机', 'url' => 'https://huawei.com']],
                ],
                false, true, 'qqpay', 'item',
            ],
            'transform the Stringable values' => [
                [
                    'appid' => 'wx2421b1c4370ec43b',
                    'body' => 'dummybot',
                    'mch_id' => '10000100',
                    'finished' => true,
                    'amount' => 100,
                    'recevier' => new class {
                        public function __toString(): string {
                            return json_encode(['type' => 'MERCHANT_ID', 'account' => '190001001']) ?: '';
                        }
                    },
                ],
                true, false, 'xml', 'item',
            ]
        ];
    }

    /**
     * @dataProvider arrayToXmlDataProvider
     * @param array<string,int|string|mixed> $data
     * @param bool $headless
     * @param bool $indent
     * @param string $root
     * @param string $item
     */
    public function testToXml(array $data, bool $headless, bool $indent, string $root, string $item): void
    {
        $xml = Transformer::toXml($data, $headless, $indent, $root, $item);
        self::assertIsString($xml);
        self::assertNotEmpty($xml);

        if ($headless) {
            self::assertStringNotContainsString('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        } else {
            self::assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        }

        if ($indent) {
            self::assertGreaterThanOrEqual(preg_match('#\n#', $xml), 2);
        } else {
            self::assertLessThanOrEqual(preg_match('#\n#', $xml), 0);
        }

        $tag = preg_quote($root);
        $pattern = '#(?:<\?xml[^>]+\?>\n?)?<' . $tag . '>.*</' . $tag . '>\n?#smu';
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression($pattern, $xml);
        } else {
            self::assertRegExp($pattern, $xml);
        }
    }
}
