<?php

namespace App\Flows;

use App\Enums\ChatState;
use App\DTO\ChatContextDTO;
use App\Repositories\ProductRepository;

class ProductFlow extends BaseFlow
{
    public function __construct (
        private ProductRepository $productRepository
    ) {}

    public function getInitialState(): ChatState
    {
        return ChatState::AWAITING_PRODUCT_NAME;
    }

    public function handle(ChatContextDTO $context): array
    {
        return match($context->currentState()) {
            ChatState::AWAITING_PRODUCT_NAME => $this->handleProductName($context),
            ChatState::AWAITING_PRODUCT_VALUE => $this->handleProductValue($context),
            ChatState::AWAITING_PRODUCT_CATEGORY => $this->handleProductCategory($context),
            ChatState::AWAITING_PRODUCT_CONFIRMATION => $this->handleConfirmation($context),
            default => $this->sendMessage('Estado inválido', ChatState::MAIN_MENU),
        };
    }

    private function handleProductName(ChatContextDTO $context): array
    {
        $productName = trim($context->userMessage());
        if (empty($productName)) {
            return $this->sendMessage(
                'Nome inválido. Por favor, digite o nome do produto:',
                ChatState::AWAITING_PRODUCT_NAME
            );
        }

        $context->setData('product_name', $productName);
        return $this->sendMessage(
            "Produto: {$productName}\n\n💰 Agora me diga o valor:",
            ChatState::AWAITING_PRODUCT_VALUE
        );
    }

    private function handleProductValue(ChatContextDTO $context): array
    {
        $value = str_replace([',', 'R$', ' '], ['', '', ''], $context->userMessage());
        if (!is_numeric($value) || $value <= 0) {
            return $this->sendMessage(
                'Valor inválido. Por favor, digite um valor válido:',
                ChatState::AWAITING_PRODUCT_VALUE
            );
        }

        $context->setData('product_value', $value);
        return $this->sendMessage(
            "Agora me diga a categoria do produto:\n1. Alimentação\n2. Transporte\n3. Lazer\n4. Outros",
            ChatState::AWAITING_PRODUCT_CATEGORY
        );
    }

    private function handleProductCategory(ChatContextDTO $context): array
    {
        $categories = ['Alimentação', 'Transporte', 'Lazer', 'Outros'];
        $choice = (int)$context->userMessage;

        if ($choice < 1 || $choice > 4) {
            return $this->sendMessage(
                '❌ Opção inválida. Escolha de 1 a 4:',
                ChatState::AWAITING_PRODUCT_CATEGORY
            );
        }

        $category = $categories[$choice - 1];
        $context->setData('product_category', $category);

        $name = $context->getData('product_name');
        $value = $context->getData('product_value');

        return $this->sendMessage(
            "📝 Confirme os dados:\n\n🏷️ Produto: {$name}\n💰 Valor: R$ " . number_format($value, 2, ',', '.') . "\n📂 Categoria: {$category}\n\nDigite 'sim' para confirmar ou 'não' para cancelar:",
            ChatState::AWAITING_PRODUCT_CONFIRMATION
        );
    }

    private function handleConfirmation(ChatContextDTO $context): array
    {
        $response = strtolower(trim($context->userMessage));

        if ($response === 'sim') {
            $this->productRepository->create([
                'user_id' => $context->userId,
                'name' => $context->getData('product_name'),
                'value' => $context->getData('product_value'),
                'category' => $context->getData('product_category'),
            ]);

            $context->clearData();

            return $this->sendMessage(
                "✅ Produto cadastrado com sucesso!\n\nO que deseja fazer agora?\n1. Cadastrar outro produto\n2. Ver meus produtos\n3. Sair",
                ChatState::MAIN_MENU
            );
        }

        $context->clearData();
        return $this->sendMessage(
            '❌ Cadastro cancelado.',
            ChatState::MAIN_MENU
        );
    }

}
