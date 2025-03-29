FROM oven/bun:1.0

WORKDIR /app

# Copiar apenas os arquivos necessários para instalar dependências
COPY package*.json bun.lockb* ./

# Instalar dependências
RUN bun install

# Copiar o resto dos arquivos
COPY . .

# Expor a porta que o Vite utiliza
EXPOSE 3000

# Comando para iniciar o servidor de desenvolvimento
CMD ["bun", "run", "dev", "--host", "0.0.0.0"]