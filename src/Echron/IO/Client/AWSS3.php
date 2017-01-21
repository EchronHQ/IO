<?php
declare(strict_types = 1);
namespace Echron\IO\Client;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileStatCollection;
use GuzzleHttp\Psr7\Stream;
use phpseclib\Net\SFTP as SFTPClient;

/**
 * http://docs.aws.amazon.com/aws-sdk-php/v3/guide/getting-started/basic-usage.html
 */
class AWSS3 extends Base
{
    private $s3Client, $bucket, $region = 'eu-west-1';

    public function __construct(string $bucket, $credentials, string $region = 'eu-west-1')
    {
        $this->bucket = $bucket;
        //$provider = CredentialProvider::defaultProvider();
        $this->s3Client = new S3Client([
            'version'     => 'latest',
            'region'      => $this->region,
            'credentials' => $credentials,
        ]);

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

    public function createBucket(string $bucket)
    {

        $result = $this->s3Client->createBucket([
            'Bucket' => $bucket,
        ]);
        $this->s3Client->waitUntil('BucketExists', ['Bucket' => $bucket]);
    }

    public function clearBucket(string $bucket)
    {

        $objects = $this->listContent($bucket);

        foreach ($objects as $object) {
            //TODO: what if we remove a different bucket than the one selected
            $this->delete($object->getPath());
            // echo $object->getPath() . PHP_EOL;
        }
        //List objects in bucket
//        $result = $this->s3Client->deleteObject([
//            'Bucket'       => '<string>',
//            // REQUIRED
//            'Key'          => '<string>',
//            // REQUIRED
//            'MFA'          => '<string>',
//            'RequestPayer' => 'requester',
//            'VersionId'    => '<string>',
//        ]);

    }

    private function listContent(string $bucket): FileStatCollection
    {
        $result = $this->s3Client->listObjects([
            'Bucket' => $bucket,

        ]);

        $collection = new FileStatCollection();
        if ($result->hasKey('Contents')) {
            $objects = $result->get('Contents');
            if ($objects !== null) {

                foreach ($objects as $object) {
                    $collection->add($this->objectInfoToFileStat($object));
                }
            }
        }

        return $collection;
    }

    private function objectInfoToFileStat(array $info): FileStat
    {

        if (!isset($info['Key'])) {
            throw new \Exception('Unable to parse object info to stat, key property not found');
        }

        $fileStat = new FileStat($info['Key']);
        if (isset($info['LastModified'])) {
            /** @var \Aws\Api\DateTimeResult $lastModified */
            $lastModified = $info['LastModified'];
            $fileStat->setChangeDate($lastModified->getTimestamp());
        }
        if (isset($info['Size'])) {
            $bytes = intval($info['Size']);
            $fileStat->setBytes($bytes);
        }

        return $fileStat;
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
        throw new \Exception('Not implemented');
    }

    public function deleteBucket(string $bucket)
    {
        //TODO: handle exceptions
        $this->s3Client->deleteBucket(['Bucket' => $bucket]);
        $this->s3Client->waitUntil('BucketNotExists', ['Bucket' => $bucket]);
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

}
