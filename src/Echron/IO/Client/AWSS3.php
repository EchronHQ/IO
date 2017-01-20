<?php
declare(strict_types = 1);
namespace Echron\IO\Client;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Echron\IO\Data\FileStat;
use GuzzleHttp\Psr7\Stream;
use phpseclib\Net\SFTP as SFTPClient;

/**
 * http://docs.aws.amazon.com/aws-sdk-php/v3/guide/getting-started/basic-usage.html
 */
class AWSS3 extends Base
{
    private $s3Client, $bucket = 'app.io', $region = 'eu-west-1';

    public function __construct()
    {
        //$provider = CredentialProvider::defaultProvider();
        $this->s3Client = new S3Client([
            'version'     => 'latest',
            'region'      => $this->region,
            'credentials' => [
                'key'    => 'AKIAJLWY2ODOND3HSPSQ',
                'secret' => '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',
            ],
        ]);

//        $this->s3Client->getbucket
//        $result = $this->s3Client->createBucket([
//            'Bucket'             => $this->bucket,
//            'LocationConstraint' => $this->region,
//        ]);
//        $this->s3Client->waitUntil('BucketExists', ['Bucket' => $this->bucket]);
    }

    public function push(string $local, string $remote)
    {
        $options = [
            'Bucket'     => $this->bucket,
            'Key'        => $remote,
            'SourceFile' => $local,
            'Metadata'   => [],
        ];
        $this->s3Client->putObject($options);
    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        $fileStat = new FileStat($remote);
        try {
            $result = $this->s3Client->headObject([
                // Bucket is required
                'Bucket' => $this->bucket,
                //            'IfMatch' => 'string',
                //            'IfModifiedSince' => 'mixed type: string (date format)|int (unix timestamp)|\DateTime',
                //            'IfNoneMatch' => 'string',
                //            'IfUnmodifiedSince' => 'mixed type: string (date format)|int (unix timestamp)|\DateTime',
                // Key is required
                'Key'    => $remote,
                //            'Range' => 'string',
                //            'VersionId' => 'string',
                //            'SSECustomerAlgorithm' => 'string',
                //            'SSECustomerKey' => 'string',
                //            'SSECustomerKeyMD5' => 'string',
                //            'RequestPayer' => 'string',
            ]);

            if ($result->hasKey('LastModified')) {
                /** @var \Aws\Api\DateTimeResult $lastModified */
                $lastModified = $result->get('LastModified');
                $fileStat->setChangeDate($lastModified->getTimestamp());
            }
            if ($result->hasKey('ContentLength')) {
                $bytes = intval($result->get('ContentLength'));
                $fileStat->setBytes($bytes);
            }

            $fileStat->setExists(true);
        } catch (S3Exception $ex) {
            $fileStat->setExists(false);
        }

        return $fileStat;

    }

    public function remoteFileExists(string $remote): bool
    {
        return $this->getRemoteFileStat($remote)
                    ->getExists();
    }

    public function pull(string $remote, string $local)
    {
        $result = $this->s3Client->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $remote,
        ]);

        if ($result->hasKey('Body')) {
            $body = $result->get('Body');
            if ($body instanceof Stream) {
                $content = $body->getContents();
                file_put_contents($local, $content);
            }
        }

    }

    public function delete(string $remote)
    {
        $result = $this->s3Client->deleteObject([
            'Bucket' => $this->bucket,
            'Key'    => $remote,
        ]);
        //var_dump($result);
    }

    public function setRemoteChangeDate(string $remote, int $changeDate)
    {
        // TODO: Implement setRemoteChangeDate() method.
    }
}
