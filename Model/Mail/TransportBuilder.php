<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 28/10/2015
 * Time: 10:54
 */
namespace Magenest\UltimateFollowupEmail\Model\Mail;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{

    protected $body;

    protected $subject;

    protected $templateId;


    public function getMessage()
    {
        return $this->message;
    }


    /**
     * @param $params
     */
    public function createAttachment($params,$transport = false)
    {
        if (!isset($params['name']) || !isset($params['body']) || !isset($params['label'])) {
            return;
        }
        $info = pathinfo($params['name']);
        if (!isset($info['extension'])) {
            return;
        }
        switch ($info['extension']) {
            case 'gif':
                $type = 'image/gif';
                break;
            case 'jpg':
            case 'jpeg':
                $type = 'image/jpg';
                break;
            case 'png':
                $type = 'image/png';
                break;
            case 'pdf':
                $type = 'application/pdf';
                break;
            case 'txt':
                $type = 'text/plain';
                break;
            default:
                $type = 'application/octet-stream';
        }

        if($transport === false) {

            $this->message->createAttachment(
                $params['body'],
                $type,
                \Zend_Mime::DISPOSITION_ATTACHMENT,
                \Zend_Mime::ENCODING_BASE64,
                $params['label']
            );

        }else{
            $this->addAttachment($params,$transport);
        }

        return $this;

    }

    /**
     * @param $params
     * @param $transport \Magento\Framework\Mail\TransportInterface
     * @throws \Exception
     */
    public function addAttachment($params,$transport){
        $zendPart = $this->createZendMimePart($params);
        $parts = $transport->getMessage()->getBody()->addPart($zendPart);
        $transport->getMessage()->setBody($parts);
    }

    protected function createZendMimePart($params){
        if(class_exists('Zend\Mime\Mime') && class_exists('Zend\Mime\Part')) {
            $type = isset($params['type']) ? $params['type'] : \Zend\Mime\Mime::TYPE_OCTETSTREAM;
            $part = new \Zend\Mime\Part(isset($params['body'])?$params['body']:"");
            $part->type = $type;
            $part->filename = isset($params['label'])?$params['label']:"";
            $part->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
            $part->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
            return $part;
        }else{
            throw new \Exception("Missing Zend Framework Source");
        }
    }

    public function setMessageContent($body, $subject)
    {
        $this->body    = $body;
        $this->subject = $subject;
    }

    public function getTemplateChoosed($templateId){

        $templateData = $this->templateFactory->get($templateId);
        $templateData->setVars($this->templateVars)->setOptions($this->templateOptions);
        return $templateData;
    }

    public function reset()
    {
        return parent::reset(); // TODO: Change the autogenerated stub
    }
}
