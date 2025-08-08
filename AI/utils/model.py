from langchain_community.embeddings import SentenceTransformerEmbeddings
from google import genai
from langchain_google_genai import ChatGoogleGenerativeAI
import os
from dotenv import load_dotenv

load_dotenv()

embeddings = SentenceTransformerEmbeddings(
    model_name="sentence-transformers/all-MiniLM-L6-v2",
)

response_llm = ChatGoogleGenerativeAI(model= "gemini-2.0-flash")
