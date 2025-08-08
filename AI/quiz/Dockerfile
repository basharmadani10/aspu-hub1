FROM python:3.9-slim
WORKDIR /app
COPY . /app
RUN pip install --no-cache-dir -r requirements.txt
EXPOSE 9001
ENV FLASK_APP=generate_quiz.py
ENV FLASK_ENV=production
CMD ["flask", "run", "--host=0.0.0.0", "--port=9001"]
