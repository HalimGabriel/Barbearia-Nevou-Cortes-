Instruções rápidas para rodar localmente:

1. Crie o banco de dados:
   - Execute o arquivo database.sql no seu MySQL (ex: via phpMyAdmin, MySQL CLI ou Workbench).

2. Configure variáveis de ambiente (opcional) ou edite config/conexao.php:
   - DB_HOST, DB_NAME, DB_USER, DB_PASS

3. Rode com PHP built-in server (exemplo):
   - Entre na pasta site-nevou-cortes e rode:
     php -S 127.0.0.1:8000

4. Acesse:
   - http://127.0.0.1:8000/index.php

Observações:
- Senhas são armazenadas com password_hash.
- Use o painel admin.php para visualizar registros.
- Arquivos actions/* contêm operações de registro, login, logout e agendamento.
