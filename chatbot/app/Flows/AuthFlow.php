<?php

namespace App\Flows;

use App\Enums\ChatState;
use App\DTO\ChatContextDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthFlow extends BaseFlow
{
    public function getInitialState(): ChatState
    {
        return ChatState::AWAITING_LOGIN_EMAIL;
    }

    public function handle(ChatContextDTO $context): array
    {
        return match($context->currentState) {
            ChatState::AWAITING_LOGIN_EMAIL => $this->handleLoginEmail($context),
            ChatState::AWAITING_LOGIN_PASSWORD => $this->handleLoginPassword($context),

            ChatState::AWAITING_REGISTER_NAME => $this->handleRegisterName($context),
            ChatState::AWAITING_REGISTER_EMAIL => $this->handleRegisterEmail($context),
            ChatState::AWAITING_REGISTER_PASSWORD => $this->handleRegisterPassword($context),

            default => $this->sendMessage('Estado inválido no fluxo de autenticação', ChatState::IDLE),
        };
    }


    private function handleLoginEmail(ChatContextDTO $context): array
    {
        $email = trim($context->userMessage);

        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->sendMessage(
                '❌ Email inválido. Por favor, digite um email válido:',
                ChatState::AWAITING_LOGIN_EMAIL
            );
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->sendMessage(
                "❌ Email não encontrado.\n\n" .
                "Digite:\n" .
                "1 - Tentar outro email\n" .
                "2 - Fazer cadastro",
                ChatState::IDLE
            );
        }

        $context->setData('login_email', $email);
        $context->setData('login_user_id', $user->id);

        return $this->sendMessage(
            "✅ Email encontrado!\n\n🔒 Digite sua senha:",
            ChatState::AWAITING_LOGIN_PASSWORD
        );
    }

    private function handleLoginPassword(ChatContextDTO $context): array
    {
        $password = $context->userMessage;
        $userId = $context->getData('login_user_id');

        $user = User::find($userId);

        if (!$user || !Hash::check($password, $user->password)) {
            return $this->sendMessage(
                "❌ Senha incorreta.\n\n" .
                "Digite:\n" .
                "1 - Tentar novamente\n" .
                "2 - Esqueci minha senha\n" .
                "3 - Voltar ao menu",
                ChatState::IDLE
            );
        }

        // Login bem-sucedido! Limpa dados temporários
        $context->clearData();

        return $this->sendMessage(
            "✅ Login realizado com sucesso!\n\n" .
            "Bem-vindo(a), {$user->name}! 👋\n\n" .
            "O que deseja fazer?\n" .
            "1 - Cadastrar produto\n" .
            "2 - Ver meus produtos\n" .
            "3 - Relatórios\n" .
            "4 - Sair",
            ChatState::MAIN_MENU
        );
    }

    private function handleRegisterName(ChatContextDTO $context): array
    {
        $name = trim($context->userMessage);

        if (strlen($name) < 3) {
            return $this->sendMessage(
                '❌ Nome muito curto. Digite seu nome completo:',
                ChatState::AWAITING_REGISTER_NAME
            );
        }

        $context->setData('register_name', $name);

        return $this->sendMessage(
            "✅ Nome: {$name}\n\n📧 Agora digite seu email:",
            ChatState::AWAITING_REGISTER_EMAIL
        );
    }

    private function handleRegisterEmail(ChatContextDTO $context): array
    {
        $email = trim($context->userMessage);

        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first('email');

            if (str_contains($message, 'unique')) {
                return $this->sendMessage(
                    "❌ Este email já está cadastrado.\n\n" .
                    "Digite:\n" .
                    "1 - Fazer login\n" .
                    "2 - Tentar outro email",
                    ChatState::IDLE
                );
            }

            return $this->sendMessage(
                '❌ Email inválido. Digite um email válido:',
                ChatState::AWAITING_REGISTER_EMAIL
            );
        }

        $context->setData('register_email', $email);

        return $this->sendMessage(
            "✅ Email: {$email}\n\n" .
            "🔒 Agora crie uma senha (mínimo 6 caracteres):",
            ChatState::AWAITING_REGISTER_PASSWORD
        );
    }

    private function handleRegisterPassword(ChatContextDTO $context): array
    {
        $password = $context->userMessage;

        if (strlen($password) < 6) {
            return $this->sendMessage(
                '❌ Senha muito curta. Digite no mínimo 6 caracteres:',
                ChatState::AWAITING_REGISTER_PASSWORD
            );
        }

        try {
            $user = User::create([
                'name' => $context->getData('register_name'),
                'email' => $context->getData('register_email'),
                'password' => Hash::make($password),
            ]);

            $context->clearData();

            return $this->sendMessage(
                "🎉 Cadastro realizado com sucesso!\n\n" .
                "Bem-vindo(a), {$user->name}! 👋\n\n" .
                "Você já está logado(a). O que deseja fazer?\n" .
                "1 - Cadastrar produto\n" .
                "2 - Ver meus produtos\n" .
                "3 - Relatórios\n" .
                "4 - Sair",
                ChatState::MAIN_MENU
            );

        } catch (\Exception $e) {
            return $this->sendMessage(
                "❌ Erro ao criar conta. Tente novamente mais tarde.",
                ChatState::IDLE
            );
        }
    }
}
