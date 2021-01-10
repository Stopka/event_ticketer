<?php

declare(strict_types=1);


namespace Ticketer\Modules\ApiModule\Responses;


use Nette\Application\IResponse as ApplicationResponseInterface;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Utils\Html;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\BufferedOutput;

class ConsoleOutputResponse implements ApplicationResponseInterface
{
    private int $result;
    private BufferedOutput $output;
    private AnsiToHtmlConverter $converter;
    private string $title;

    public function __construct(int $result, BufferedOutput $output, string $title = 'Console output')
    {
        $this->result = $result;
        $this->output = $output;
        $this->converter = new AnsiToHtmlConverter();
        $this->title = $title;
    }


    function send(IRequest $httpRequest, IResponse $httpResponse): void
    {
        $httpResponse->setCode(
            $this->result === Command::SUCCESS
                ? $httpResponse::S200_OK
                : $httpResponse::S500_INTERNAL_SERVER_ERROR
        );
        $httpResponse->setContentType('text/html', 'UTF-8');
        $content = $this->converter->convert(
            $this->output->fetch()
        );
        $content = str_replace("\n",'<br />',$content);
        echo "<!doctype html>
<html lang=en>
    <head>
        <meta charset=utf-8>
        <title>{$this->title}</title>
        <style>
            html{ background: black; color: white }
        </style>
    </head>
    <body>
        $content
    </body>
</html>";
    }
}
