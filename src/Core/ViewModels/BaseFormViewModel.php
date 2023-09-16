<?php declare(strict_types=1);

namespace Bakle\LskCore\Core\ViewModels;

use Bakle\LskCore\Core\Entities\BaseEntity;
use Bakle\LskCore\Core\Enums\FormTypes;
use Bakle\LskCore\Core\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

abstract class BaseFormViewModel
{
    protected array $models;

    abstract protected function getEntityClass(): string;

    public function __construct(protected readonly FormTypes $formType, protected readonly Model $model, ...$models)
    {
        $this->models = $models;
    }

    public function build(): array
    {
        return [
            'entity' => $this->getEntity(),
            'form' => $this->getForm(),
            'title' => $this->getTitle(),
            ...$this->getExtraAttributes()
        ];
    }


    public function getForm(): Form
    {
        return new Form($this->resolveFormUrl(), $this->formType);
    }

    protected function getExtraAttributes(): array
    {
        return [];
    }

    protected function getEntity(): BaseEntity
    {
        $class = new ReflectionClass($this->getEntityClass());

        return $class->newInstance($this->model, $this->models);
    }

    protected function resolveFormUrl(): string
    {
        return match ($this->formType) {
            FormTypes::EDIT => $this->getEntity()->url()->update(),
            default => $this->getEntity()->url()->store(),
        };
    }

    private function getFormType(): FormTypes
    {
        return $this->formType;
    }

    private function getTitle(): string
    {
        $type = $this->isCreateFormType() ? trans('Create') : trans('Edit');

        return $type . ' ' . $this->getEntity()->getModelName();
    }

    private function isCreateFormType(): bool
    {
        return $this->getFormType() == FormTypes::CREATE;
    }
}
