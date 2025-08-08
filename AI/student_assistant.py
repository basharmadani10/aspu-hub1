from flask import Flask, request, jsonify
from utils import embeddings, response_llm
from prompts.system_prompts import QA_PROMPT, contextualize_q_prompt
from langchain_community.document_loaders import TextLoader
from langchain_text_splitters import RecursiveCharacterTextSplitter
from langchain_community.vectorstores import Chroma
import os
from langchain.chains import (
    create_history_aware_retriever,
    create_retrieval_chain,
)
from langchain.chains.combine_documents import create_stuff_documents_chain
from langchain_community.chat_message_histories import ChatMessageHistory
from langchain_core.chat_history import BaseChatMessageHistory
from langchain_core.runnables.history import RunnableWithMessageHistory
from langchain_core.messages import AIMessage
from flask_cors import CORS
store = {}
VECTORSTORE_DIR = "./chroma_db_lg"


app = Flask(__name__)
CORS(app)

def setup_combined_document_store(split_dir: str, persist_directory: str):
    """Combine full-chunk and split-chunk documents into one Chroma vector store."""
    documents = []
    encodings_to_try = ["utf-8", "cp1256", "latin-1", "iso-8859-1"]
    supported_extensions = [".txt"]

    if os.path.exists(split_dir):
        print(f"\nLoading documents from {split_dir} with segmentation...")
        for root, _, files in os.walk(split_dir):
            for file in files:
                if any(file.lower().endswith(ext) for ext in supported_extensions):
                    file_path = os.path.join(root, file)
                    loaded = False
                    for enc in encodings_to_try:
                        try:
                            loader = TextLoader(file_path, encoding=enc)
                            raw_docs = loader.load()
                            splitter = RecursiveCharacterTextSplitter(chunk_size=600, chunk_overlap=100)
                            split_docs = splitter.split_documents(raw_docs)
                            documents.extend(split_docs)
                            loaded = True
                            break
                        except Exception:
                            continue
                    if not loaded:
                        print(f"Failed to load and split {file_path} with any of the encodings.")

    if not documents:
        print("No readable documents were found in the specified folder.")
        return None

    print(f"\nTotal documents/parts loaded: {len(documents)}")

    try:
        print(f"\nCreating vector store in {persist_directory}...")
        if os.path.isdir(persist_directory) and os.listdir(persist_directory):
            print(f"Found an existing vector store in {persist_directory}, loading it now...")
            try:
                return Chroma(persist_directory=persist_directory, embedding_function=embeddings)
            except Exception as e:
                print(f"⚠️ Failed to load previous vector store: {e}, recreating it now...")

        vectorstore = Chroma.from_documents(
            documents=documents,
            embedding=embeddings,
            persist_directory=persist_directory
        )
        print(f"Successfully processed {len(documents)} document(s)/part(s).")
        print("Chroma Vector Store created successfully.")
        return vectorstore
    except Exception as e:
        print(f"Failed to create vector store: {e}")
        return None


def initialize_app():
    vectorstore = setup_combined_document_store(
        split_dir="lectures",
        persist_directory=VECTORSTORE_DIR
    )
    if vectorstore is None:
        raise ValueError("Failed to initialize vector store. Ensure valid text files exist in the folder.")

    retriever = vectorstore.as_retriever(search_type="similarity", search_kwargs={"k": 5})

    history_aware_retriever = create_history_aware_retriever(
        response_llm,
        retriever,
        contextualize_q_prompt,
    )
    question_answer_chain = create_stuff_documents_chain(response_llm, QA_PROMPT)
    rag_chain = create_retrieval_chain(history_aware_retriever, question_answer_chain)

    def get_session_history(session_id: str) -> BaseChatMessageHistory:
        if session_id not in store:
            store[session_id] = ChatMessageHistory()
        return store[session_id]

    conversational_rag_chain = RunnableWithMessageHistory(
        rag_chain,
        get_session_history,
        input_messages_key="input",
        history_messages_key="chat_history",
        output_messages_key="answer",
    )

    return conversational_rag_chain


conversational_rag_chain = initialize_app()

@app.route('/ask', methods=['POST'])
def ask_question():
    try:
        data = request.json
        question = data.get("question", "").strip()
        session_id = data.get("session_id", "default_session")

        if not question:
            return jsonify({"error": "Missing 'question' field"}), 400

        result = conversational_rag_chain.invoke(
            {"input": question},
            config={"configurable": {"session_id": session_id}}
        )

        answer = result.get("answer")
        sources = result.get("source_documents", [])

        return jsonify({
            "answer": answer,
            "sources": [getattr(doc, "metadata", {}).get("source", "N/A") for doc in sources]
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500


def main_lg():
    conversational_rag_chain = initialize_app()

    print("\n--- RAG application ready to receive queries ---")
    while True:
        user_query = input("\nEnter your question (or 'exit' to quit): ").strip()
        if user_query.lower() in ['exit', 'q']:
            print("Exiting. Goodbye!")
            break

        result = conversational_rag_chain.invoke(
            {"input": user_query},
            config={"configurable": {"session_id": "student_assistant"}}
        )
        answer = result.get("answer")
        sources = result.get("source_documents", [])

        print("\nAnswer:")
        print(answer)
        if sources:
            print("\nSources:")
            for doc in sources:
                print(f"- Page: {getattr(doc, 'metadata', {}).get('source', 'N/A')}")

if __name__ == "__main__":
    app.run(debug=True, host='0.0.0.0', port=9000)

