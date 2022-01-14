<?php
namespace Swissup\Email\Mail\Transport;

use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterface;

use SlmMail\Mail\Transport\HttpTransport;
use SlmMail\Service\MandrillService;

use Swissup\Email\Api\Data\ServiceInterface;
use Swissup\Email\Mail\Message\Convertor;

class Mandrill implements TransportInterface
{
    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * @var HttpTransport
     */
    protected $transport;

    /**
     *
     * @param array $config
     * @param SesClient $client
     * @throws \InvalidArgumentException
     */
    public function __construct(
        array $config
    ) {
        $service = new MandrillService($config['password']);
        // \Zend_Debug::dump($service->pingUser()));

        $this->transport = new HttpTransport($service);
    }

    /**
     * Send a mail using this transport
     *
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendMessage()
    {
        try {
            $message = $this->message;
            $message = Convertor::fromMessage($message);

            $body = $message->getBody();
            if ($body instanceof \Laminas\Mime\Message) {
                $parts = [];
                foreach ($body->getParts() as $part) {
                    if ($part instanceof \Magento\Framework\Mail\MimePart) {
                        $parts[] = Convertor::convertMimePart($part);
                    } elseif ($part instanceof \Laminas\Mime\Part) {
                        $parts[] = $part;
                    }
               }
               $body->setParts($parts);
            }
            $this->transport->send($message);
        } catch (\Exception $e) {
            $phrase = new \Magento\Framework\Phrase($e->getMessage());
            throw new \Magento\Framework\Exception\MailException($phrase, $e);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     *
     * @param MessageInterface $message
     */
    public function setMessage($message)
    {
        // if (!$message instanceof MessageInterface) {
        //     throw new \InvalidArgumentException(
        //         'The message should be an instance of \Magento\Framework\Mail\Message'
        //     );
        // }
        $this->message = $message;
        return $this;
    }
}
