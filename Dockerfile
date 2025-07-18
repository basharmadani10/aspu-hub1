FROM node:20-alpine as builder

WORKDIR /app


COPY package*.json ./


RUN npm install


COPY . .


ARG VITE_API_BASE_URL
ARG VITE_IMAGE_BASE_URL
ARG VITE_APP_BASE_URL
ARG VITE_Generate_BASE_URL
ARG VITE_Assistant_BASE_URL
ARG VITE_Admin_BASE_URL


ENV VITE_API_BASE_URL=$VITE_API_BASE_URL
ENV VITE_IMAGE_BASE_URL=$VITE_IMAGE_BASE_URL
ENV VITE_APP_BASE_URL=$VITE_APP_BASE_URL
ENV VITE_Generate_BASE_URL=$VITE_Generate_BASE_URL
ENV VITE_Assistant_BASE_URL=$VITE_Assistant_BASE_URL
ENV VITE_Admin_BASE_URL=$VITE_Admin_BASE_URL

RUN npm run build


FROM nginx:alpine

COPY --from=builder /app/dist /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
