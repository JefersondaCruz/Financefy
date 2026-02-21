<?php

namespace App\DTO;

class ProductDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $name,
        public readonly float $value,
        public readonly string $category,
        public readonly ?string $description = null,
        public readonly ?\DateTime $purchaseDate = null,
    ) {}

    /**
     * Cria DTO a partir de array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'] ?? throw new \InvalidArgumentException('user_id é obrigatório'),
            name: $data['name'] ?? throw new \InvalidArgumentException('name é obrigatório'),
            value: (float) ($data['value'] ?? throw new \InvalidArgumentException('value é obrigatório')),
            category: $data['category'] ?? throw new \InvalidArgumentException('category é obrigatório'),
            description: $data['description'] ?? null,
            purchaseDate: isset($data['purchase_date']) ? new \DateTime($data['purchase_date']) : null,
        );
    }

    /**
     * Cria DTO a partir de Request (HTTP)
     */
    public static function fromRequest(\Illuminate\Http\Request $request, int $userId): self
    {
        return new self(
            userId: $userId,
            name: $request->input('name'),
            value: (float) $request->input('value'),
            category: $request->input('category'),
            description: $request->input('description'),
            purchaseDate: $request->has('purchase_date')
                ? new \DateTime($request->input('purchase_date'))
                : null,
        );
    }

    /**
     * Cria DTO a partir do contexto do chatbot
     */
    public static function fromChatContext(int $userId, array $contextData): self
    {
        return new self(
            userId: $userId,
            name: $contextData['product_name'] ?? throw new \InvalidArgumentException('product_name não encontrado'),
            value: (float) ($contextData['product_value'] ?? throw new \InvalidArgumentException('product_value não encontrado')),
            category: $contextData['product_category'] ?? throw new \InvalidArgumentException('product_category não encontrado'),
            description: $contextData['product_description'] ?? null,
            purchaseDate: isset($contextData['purchase_date'])
                ? new \DateTime($contextData['purchase_date'])
                : new \DateTime(), // Data atual como padrão
        );
    }

    /**
     * Converte para array (para salvar no banco)
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'name' => $this->name,
            'value' => $this->value,
            'category' => $this->category,
            'description' => $this->description,
            'purchase_date' => $this->purchaseDate?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Formata valor para exibição
     */
    public function getFormattedValue(): string
    {
        return 'R$ ' . number_format($this->value, 2, ',', '.');
    }

    /**
     * Formata data para exibição
     */
    public function getFormattedDate(): string
    {
        return $this->purchaseDate?->format('d/m/Y') ?? 'Não informada';
    }

    /**
     * Valida se os dados são válidos
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->name) || strlen($this->name) < 3) {
            $errors[] = 'Nome deve ter no mínimo 3 caracteres';
        }

        if ($this->value <= 0) {
            $errors[] = 'Valor deve ser maior que zero';
        }

        $validCategories = ['Alimentação', 'Transporte', 'Lazer', 'Saúde', 'Educação', 'Outros'];
        if (!in_array($this->category, $validCategories)) {
            $errors[] = 'Categoria inválida';
        }

        return $errors;
    }

    /**
     * Verifica se é válido
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }
}
