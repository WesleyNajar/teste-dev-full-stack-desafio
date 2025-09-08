FROM node:18-alpine

# Instalar dependências do sistema
RUN apk add --no-cache git

# Definir diretório de trabalho
WORKDIR /app

# Copiar package.json e package-lock.json
COPY package*.json ./

# Instalar dependências (incluindo dev dependencies para Vite)
RUN npm install

# Copiar código fonte
COPY . .

# Criar arquivo .env se não existir
RUN if [ ! -f .env ]; then cp env.example .env; fi

# Verificar se Vite foi instalado
RUN ls -la node_modules/.bin/ | grep vite || echo "Vite não encontrado"

# Configurar permissões
RUN chown -R node:node /app
USER node

# Expor porta 3000
EXPOSE 3000

# Comando para desenvolvimento
CMD ["./node_modules/.bin/vite", "--host", "0.0.0.0", "--port", "3000"]
