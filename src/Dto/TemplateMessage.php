<?php

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Concerns\WhatsappMessage;
use Saloon\Traits\Makeable;

class TemplateMessage implements WhatsappMessage
{
    use Makeable;

    public string $templateName;
    public string $language;

    public array $bodyParameters = [];

    public function __construct(string $templateName, string $language)
    {
        $this->templateName = $templateName;
        $this->language = $language;
    }

    public function withBodyParameters(array $bodyParameters): self
    {
        $this->bodyParameters = $bodyParameters;
        return $this;
    }


    public function toRequestBody(): array
    {
        $body = [
            'type' => 'template',
            'template' => [
                'name' => $this->templateName,
                'language' => [
                    'code' => $this->language,
                ],
            ],
        ];

        if ($this->bodyParameters !== []) {

            $bodyParameters = [];

            foreach ($this->bodyParameters as $key => $text) {
                $parameter = [
                    'type' => 'text',
                    'text' => $text,
                ];

                if (!is_numeric($key)) {
                    $parameter['parameter_name'] = $key;
                }

                $bodyParameters[] = $parameter;
            }

            $body['components'][] = [
                'type' => 'body',
                'parameters' => $bodyParameters,
            ];
        }

        return $body;
    }
}
