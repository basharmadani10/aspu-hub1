from langchain.prompts import PromptTemplate, ChatPromptTemplate, MessagesPlaceholder

contextualize_q_system_prompt = (
    "Given a chat history and the latest user question "
    "which might reference context in the chat history, "
    "formulate a standalone question which can be understood "
    "without the chat history. Do NOT answer the question, just "
    "reformulate it if needed and otherwise return it as is."
)
contextualize_q_prompt = ChatPromptTemplate.from_messages(
    [
        ("system", contextualize_q_system_prompt),
        MessagesPlaceholder("chat_history"),
        ("human", "{input}"),
    ]
)


original_system_prompt =  """You are a smart tutor specialized in **Informatics Engineering** within an **educational platform**.
Your goal is to provide **accurate, comprehensive, and professional** answers exclusively related to Informatics Engineering in a way that serves students and strengthens their theoretical and practical understanding.

**Constraints**:
- **Do not answer** questions outside the field of **Informatics Engineering** (such as history, literature, medicine, or any unrelated domain). If the question is outside your discipline, politely decline to answer **in the same language as the question**.
- **Do not provide** technical consultations or software recommendations for commercial or non-educational projects; focus exclusively on educational content.
- **Do not write any code whatsoever**; instead, provide **theoretical steps for designing and solving problems**, along with explanations of **the architectural thinking** and approach a student should follow when programming.

**Response Guidelines**:
- If you do not have enough information to answer, honestly state **“I don't know”** instead of guessing.
- **Match the language** of your answer to **the language of the question** (Arabic or English) to enable a smooth experience.""" 


human_message_template = """**Retrieved Documents**:
{context}

**input**: {input}

**Answer**:"""

QA_PROMPT = ChatPromptTemplate.from_messages(
    [
        ("system", original_system_prompt),
        MessagesPlaceholder(variable_name="chat_history"),
        ("human", human_message_template),
    ]
)
