<?php

namespace DefStudio\Whatsapper\Dto;

use DefStudio\Whatsapper\Contracts\WhatsappMessage;

class TemplateMessage implements WhatsappMessage
{
    public string $templateName;

    public string $language;

    public array $bodyParameters = [];

    public array $buttonParameters = [];

    public function __construct(string $templateName, string $language)
    {
        $this->templateName = $templateName;
        $this->language = $language;
    }

    public function text(): string
    {
        //TODO
        return '';
    }

    public function withBodyParameters(array $bodyParameters): self
    {
        $this->bodyParameters = $bodyParameters;

        return $this;
    }

    public function withButtonParameters(array $buttonParameters): self
    {
        $this->buttonParameters = $buttonParameters;

        return $this;
    }

    public function withBottomButtonsParameters(array $buttonParameters): self
    {
        return $this->withButtonParameters($buttonParameters);
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

        $components = [];

        if ($this->bodyParameters !== []) {
            $components[] = [
                'type' => 'body',
                'parameters' => $this->normalizeParameters($this->bodyParameters),
            ];
        }

        if ($this->buttonParameters !== []) {
            foreach ($this->buttonParameters as $key => $button) {
                if (is_array($button) && array_key_exists('parameters', $button)) {
                    $components[] = [
                        'type' => 'button',
                        'sub_type' => $button['sub_type'] ?? 'url',
                        'index' => (string) ($button['index'] ?? (is_numeric($key) ? $key : 0)),
                        'parameters' => $this->normalizeParameters($button['parameters']),
                    ];

                    continue;
                }

                $components[] = [
                    'type' => 'button',
                    'sub_type' => 'url',
                    'index' => (string) (is_numeric($key) ? $key : 0),
                    'parameters' => $this->normalizeParameters([$button]),
                ];
            }
        }

        if ($components !== []) {
            $body['template']['components'] = $components;
        }

        return $body;
    }

    protected function normalizeParameters(array $parameters): array
    {
        $normalizedParameters = [];

        foreach ($parameters as $key => $parameterValue) {
            if (is_array($parameterValue)) {
                $parameter = $parameterValue;
            } else {
                $parameter = [
                    'type' => 'text',
                    'text' => (string) $parameterValue,
                ];
            }

            if (! is_numeric($key) && ! array_key_exists('parameter_name', $parameter)) {
                $parameter['parameter_name'] = $key;
            }

            $normalizedParameters[] = $parameter;
        }

        return $normalizedParameters;
    }
}
