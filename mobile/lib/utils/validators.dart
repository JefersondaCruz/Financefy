class Validators {
  static String? email(String? value) {
    if (value == null || value.isEmpty) return 'E-mail obrigatório';
    if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value)) {
      return 'E-mail inválido';
    }
    return null;
  }

  static String? password(String? value) {
    if (value == null || value.isEmpty) return 'Senha obrigatória';
    if (value.length < 6) return 'A senha deve ter no mínimo 6 caracteres';
    return null;
  }

  static String? phone(String? value) {
    if (value == null || value.isEmpty) return 'Telefone obrigatório';
    if (value.length < 14) return 'Telefone inválido';
    return null;
  }

  static String? requiredField(String? value) {
    if (value == null || value.isEmpty) return 'Campo obrigatório';
    return null;
  }
}
