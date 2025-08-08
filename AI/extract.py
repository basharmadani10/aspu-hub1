from google import genai
import os
import re
from tqdm import tqdm
from pdf2image import convert_from_path
from pdf2image import pdfinfo_from_path
import google.generativeai as genai
from dotenv import load_dotenv

load_dotenv()

key = os.getenv("GEMINI_KEY")
# Use the service account key to authenticate with the Google Cloud API
genai.configure(api_key=key)
model = genai.GenerativeModel("gemini-2.0-flash")

prompt = """
Analyze the provided image, which is a single page from a textbook. Your task is to extract all the content from this page and organize it as Markdown (MD) data suitable for use in Retrieval-Augmented Generation (RAG) systems.

Follow these specific instructions carefully:

1. **Output Language:** All structural output elements (such as main and subheadings, captions, image descriptions) must be in **English**.

2. **Content Extraction:** Extract all textual content including main headings, subheadings, paragraphs, lists, captions, footnotes, and any other text present on the page.

3. **Original Content Language:** Preserve the original language of the extracted text exactly as it appears on the page. If the text is in English, keep it in English. If it is in another language, retain it in that language.

4. **Markdown Structure:**
   - Use appropriate Markdown syntax (e.g., `#` for headings, `*` or `-` for lists, `>` for block quotes if applicable).
   - Reflect the visual hierarchy of the page using English Markdown headings (e.g., `## Main Section`, `### Subsection`).
   - Preserve paragraphs and blocks of text as they appear on the page.

5. **Image Handling:**
   - If the page contains one or more images, create a dedicated section using a Markdown heading such as `## Image Descriptions`.
   - Under this heading, provide a concise and objective description in English for each image present. Label them sequentially if there are multiple images (e.g., "Image 1:", "Image 2:").
   - Describe what each image depicts and its relevance to the page content.

6. **Question Handling:**
   - If the page contains any questions (e.g., review questions, exercises, discussion prompts), create a dedicated section using a Markdown heading such as `## Questions`.
   - List each question exactly as it appears on the page, preserving its original language and numbering/lettering.

7. **Output Formatting:** Structure the entire output in Markdown format. Ensure a clear separation between main content, image descriptions (if any), and questions (if any). Use English for all structural elements and descriptions, and preserve the original language of any extracted content.

8. **Focus:** Extract content **only** from the provided page. Do not add summaries, interpretations, or information not explicitly present on the page, except for the required image descriptions.

The final output must be clean, well-structured Markdown that accurately represents the content of the single textbook page, with all structural elements and descriptions in English, and all extracted content preserved in its original language.
"""



images = convert_from_path("/content/CH2 Inverted Index and Preprocessing .pdf")


print("عدد الصفحات المستخرجة:", len(images))

images = images[1:10]
starting_page = 1

for num, image in tqdm(enumerate(images), total=len(images)):
    response = model.generate_content([prompt, image])

    expected_page_number = starting_page + num
    page_header = f"--رقم الصفحة:-- {expected_page_number}\n"
    final_text = page_header + response.text

    with open("test1-10.txt", 'a', encoding='utf-8') as file:
        file.write(final_text + "\n-----------------------------------------\n\n")
