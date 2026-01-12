FROM node:20-alpine

# Install Angular CLI globally
RUN npm install -g @angular/cli

# Set working directory
WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
# Install dependencies with increased timeout for slow networks
RUN npm config set fetch-retry-maxtimeout 600000 -g && npm install

# Copy application files
COPY . .

# Expose port
EXPOSE 4200

# Start development server with hot reload
CMD ["ng", "serve", "--host", "0.0.0.0", "--port", "4200", "--poll", "2000"]