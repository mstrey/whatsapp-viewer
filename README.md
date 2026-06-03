# Visualizador de Exportação do WhatsApp

Um visualizador web de alto desempenho, focado em privacidade e segurança, projetado para ler e processar arquivos de conversas exportadas do WhatsApp (texto e mídias como `.opus`, `.pdf`, `.jpg`). 

## Requisitos Prévios

* **Docker** e **Docker Compose** (V2) instalados.
* Uma rede externa Docker configurada (por padrão, o projeto busca uma rede chamada `frontend` para integração com proxy reverso).
* Proxy reverso (ex: Traefik) rodando no host para gerenciar os certificados SSL/TLS, requisito obrigatório do Google OAuth.
* Credenciais ativas no Google Cloud Console (Client ID e Secret).

## Configuração no Console do Google Cloud

Você precisa cadastrar a URL exata (com HTTPS) no painel do Google antes de alterar o código.

Acesse o [Google Cloud Console](https://console.cloud.google.com/).

Navegue até APIs e Serviços > Credenciais.

Na seção IDs do cliente OAuth 2.0, clique no nome do seu aplicativo web.

Role até a seção URIs de redirecionamento autorizados.

Clique em ADICIONAR URI e insira o caminho da sua aplicação:
https://SERVICE.DOMAIN/oauth_callback.php

Clique em Salvar.
Anote o client id e o secret

## Configuração do Ambiente

1. Clone este repositório para o seu servidor.
2. Crie o arquivo de variáveis de ambiente chamado `.env` na raiz do projeto contendo as suas chaves e a lista de usuários autorizados:

```env
GOOGLE_CLIENT_ID=seu_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=sua_chave_secreta
GOOGLE_REDIRECT_URI=https://seu-dominio.com.br/oauth_callback.php
ALLOWED_EMAILS=email_autorizado@gmail.com,outro_email@gmail.com
```


Crie a pasta de dados onde a conversa será armazenada:

```Bash
mkdir chat_data
```
Extraia todo o conteúdo do seu arquivo ZIP gerado pelo WhatsApp (o arquivo _chat.txt e todas as mídias anexas) diretamente na raiz da pasta chat_data. Não utilize subpastas.

Para subir o ambiente, execute:

```Bash
docker compose up -d
```
A aplicação estará disponível através da regra de roteamento definida no seu proxy reverso (conforme as labels configuradas no docker-compose.yml).
