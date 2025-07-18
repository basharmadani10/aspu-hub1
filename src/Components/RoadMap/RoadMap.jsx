import React, { useState, useEffect, useRef } from "react";
import axios from "axios";
import { gsap } from "gsap";
import "./RoadMap.css";  
import HeaderApp from "../HeaderApp/HeaderApp";


const API_BASE_URL = import.meta.env.VITE_API_BASE_URL; 

const RoadMap = () => {
  const [specializationData, setSpecializationData] = useState([]);
  const [selectedMajorName, setSelectedMajorName] = useState("");
  const [selectedRoadmapType, setSelectedRoadmapType] = useState("");
  const [svg, setSvg] = useState("");
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);
  const [isRendering, setIsRendering] = useState(false);
  const [selectedSubjectReferences, setSelectedSubjectReferences] = useState([]);
  const mermaidContainerRef = useRef(null);

  useEffect(() => {
    const fetchData = async () => {
      setIsLoading(true);
      setError(null);
      try {
        // استخدم API_BASE_URL هنا
        const response = await axios.get(`${API_BASE_URL}/specialization-roadmaps`); // <--- تم التعديل
        console.log("API Specialization Roadmaps Response:", response.data);

        if (response.data && response.data.length > 0) {
          setSpecializationData(response.data);
          const firstSpecialization = response.data[0];
          setSelectedMajorName(firstSpecialization.name);
          if (firstSpecialization.roadmaps && firstSpecialization.roadmaps.length > 0) {
            setSelectedRoadmapType(firstSpecialization.roadmaps[0].type);
          } else {
            setSvg("<p class='text-center text-gray-700'>Selected specialization has no roadmaps.</p>");
          }
        } else {
          setSpecializationData([]);
          setSvg("<p class='text-center text-gray-700'>No specializations with roadmaps available from the API.</p>");
        }
      } catch (err) {
        console.error("Failed to load specialization roadmaps:", err);
        setError(`Failed to load specialization roadmaps. Please ensure the backend server is running and accessible at ${API_BASE_URL}. Error: ` + err.message); // <--- تم التعديل
        setSpecializationData([]);
        setSvg("<p class='text-center text-red-500'>Error loading specialization roadmaps. Please check your backend connection.</p>");
      } finally {
        setIsLoading(false);
      }
    };
    fetchData();
  }, []);

  useEffect(() => {
    if (!isLoading && !error && specializationData.length > 0) {
      const currentSpecialization = specializationData.find(
        (spec) => spec.name.toLowerCase() === selectedMajorName.toLowerCase()
      );

      if (currentSpecialization) {
        const selectedRoadmap = currentSpecialization.roadmaps.find(
          (roadmap) => roadmap.type === selectedRoadmapType
        );

        if (selectedRoadmap && selectedRoadmap.subjects && selectedRoadmap.subjects.length > 0) {
          console.log("Selected Roadmap Data for Mermaid:", selectedRoadmap.subjects);
          const chart = generateMermaidFromSubjects(selectedRoadmap.subjects);
          renderMermaid(chart, selectedRoadmap.subjects);
        } else {
          setSvg("<p class='text-center text-gray-700'>No roadmap found for the selected type in this specialization.</p>");
          setSelectedSubjectReferences([]);
        }
      } else {
        setSvg("<p class='text-center text-gray-700'>No specialization found for the selection.</p>");
        setSelectedSubjectReferences([]);
      }
    } else if (!isLoading && !error && specializationData.length === 0) {
      setSvg("<p class='text-center text-gray-700'>No specializations available.</p>");
      setSelectedSubjectReferences([]);
    }
  }, [specializationData, selectedMajorName, selectedRoadmapType, isLoading, error]);

  const renderMermaid = async (definition, subjectsData) => {
    setIsRendering(true);
    try {
      const mermaidModule = await import("mermaid");
      const mermaid = mermaidModule.default || mermaidModule;

      if (!mermaid.__initialized) {
        mermaid.initialize({
          startOnLoad: false,
          securityLevel: 'loose',
          theme: 'base',
          flowchart: {
            useMaxWidth: false,
            htmlLabels: true,
            curve: 'basis'
          }
        });
        mermaid.__initialized = true;
      }

      const { svg: generatedSvg } = await mermaid.render('mermaid-graph', definition);
      setSvg(generatedSvg);

      setTimeout(() => {
        const container = mermaidContainerRef.current;
        if (container) {
          const svgElement = container.querySelector(".mermaid svg");

          if (svgElement) {
            gsap.from(svgElement, {
              duration: 0.8,
              opacity: 0,
              y: 50,
              ease: "power3.out"
            });

            const handleSvgClick = (event) => {
              const clickedNode = event.target.closest('.node');
              if (clickedNode) {
                const nodeLabelElement = clickedNode.querySelector('.nodeLabel p') || clickedNode.querySelector('.label text');
                const subjectName = nodeLabelElement ? nodeLabelElement.textContent.trim() : null;

                if (subjectName) {
                  const subjectRef = subjectsData.find(s => s.name === subjectName);
                  if (subjectRef && subjectRef.references) {
                    setSelectedSubjectReferences(subjectRef.references);
                  } else {
                    setSelectedSubjectReferences([]);
                  }
                  console.log("Clicked Subject Name:", subjectName);
                  console.log("References for subject:", subjectRef ? subjectRef.references : "Not found");
                }
              }
            };
            if (container.__mermaidClickListener) {
              container.removeEventListener('click', container.__mermaidClickListener);
            }
            container.addEventListener('click', handleSvgClick);
            container.__mermaidClickListener = handleSvgClick;
          }
        }
      }, 100);
    } catch (renderError) {
      console.error("Mermaid render error:", renderError);
      setSvg(`<div class="text-red-500 p-4">Error rendering roadmap: ${renderError.message}</div>`);
    } finally {
      setIsRendering(false);
    }
  };

  const generateMermaidFromSubjects = (subjects) => {
    if (!subjects || subjects.length === 0) return "graph TD\nA[No subjects defined]";

    let graph = "graph TD\n"; // الرسم عمودي (Top-Down)
    const subjectNodes = {};

    const sortedSubjects = [...subjects].sort((a, b) => a.name.localeCompare(b.name));


    sortedSubjects.forEach((s, i) => {
      const nodeId = `S${i}`;
      subjectNodes[s.name] = nodeId;
      graph += `${nodeId}["${s.name}"]\n`; // فقط اسم المادة
    });

    // إنشاء الروابط بناءً على المتطلبات المسبقة (prerequisites)
    sortedSubjects.forEach((s) => {
      if (s.prerequisites && Array.isArray(s.prerequisites) && s.prerequisites.length > 0) {
        s.prerequisites.forEach((prereqName) => {
          const prereqNodeId = subjectNodes[prereqName];
          const currentNodeId = subjectNodes[s.name];
          if (prereqNodeId && currentNodeId) {
            if (!graph.includes(`${prereqNodeId} --> ${currentNodeId}`)) {
              graph += `${prereqNodeId} --> ${currentNodeId}\n`;
            }
          } else {
            console.warn(`Prerequisite "${prereqName}" for subject "${s.name}" not found in the current roadmap's subjects. This prerequisite will not be drawn.`);
          }
        });
      }
    });

    // إضافة التنسيق المخصص
    graph += "\nclassDef customStyle fill:#4f46e5,stroke:#FFFFFF,stroke-width:2px,color:#FFFFFF,font-weight:bold;";
    sortedSubjects.forEach((_, i) => {
      graph += `class S${i} customStyle;\n`;
    });

    // إضافة تنسيق الروابط
    graph += "linkStyle default stroke:#FFFFFF,stroke-width:2px,stroke-dasharray: 5, 5;\n";

    return graph;
  };

  const availableMajorNames = [...new Set(specializationData.map((spec) => spec.name))];

  const currentSelectedSpecialization = specializationData.find(
    (spec) => spec.name.toLowerCase() === selectedMajorName.toLowerCase()
  );
  const availableRoadmapTypes = currentSelectedSpecialization
    ? [...new Set(currentSelectedSpecialization.roadmaps.map((roadmap) => roadmap.type))]
    : [];


  return (
    <div className="min-h-screen bg-gray-50 flex flex-col items-center">
    <HeaderApp  />
      <div className="w-full max-w-6xl flex flex-col lg:flex-row gap-8 mt-8" style={{marginTop:"30px"}}>
        {/* Sidebar Controls */}
  <div className="controls-sidebar w-full lg:w-1/3">
    <h1 className="title">Specialization Roadmaps</h1>

          {error && (
            <div className="bg-red-900 text-red-300 p-4 rounded-lg">
              <p>{error}</p>
            </div>
          )}

          <div className="flex flex-col">
            <label htmlFor="type-select" className="text-lg font-semibold text-gray-300 mb-2" style={{color:"#6366f1" , marginBottom:"5px"}} >
              Select Type:
            </label>
            <select
              id="type-select"
              value={selectedRoadmapType}
              onChange={(e) => setSelectedRoadmapType(e.target.value)}
              className="p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-300" 
              disabled={isLoading || availableRoadmapTypes.length === 0}
            >
              {availableRoadmapTypes.length > 0 ? (
                availableRoadmapTypes.map((t, i) => (
                  <option key={i} value={t}>
                    {t}
                  </option>
                ))
              ) : (
                <option value="">{isLoading ? "Loading types..." : "No types available"}</option>
              )}
            </select>
          </div>

          <div className="flex flex-col">
            <label htmlFor="major-select" className="text-lg font-semibold text-gray-300 mb-2" style={{color:"#6366f1",marginTop:"15px"}}>
              Select Specialization:
            </label>
            <select
              id="major-select"
                style={{marginTop:"5px"}}
              value={selectedMajorName}
              onChange={(e) => {
                setSelectedMajorName(e.target.value);
                // عند تغيير الاختصاص، قم بتحديث نوع الـ roadmap الافتراضي
                const selectedSpec = specializationData.find(spec => spec.name.toLowerCase() === e.target.value.toLowerCase());
                if (selectedSpec && selectedSpec.roadmaps && selectedSpec.roadmaps.length > 0) {
                  setSelectedRoadmapType(selectedSpec.roadmaps[0].type);
                } else {
                  setSelectedRoadmapType(""); // لا يوجد أنواع إذا لا يوجد roadmaps
                }
              }}
              className="p-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-300"
              disabled={isLoading || availableMajorNames.length === 0}
            >
              {availableMajorNames.length > 0 ? (
                availableMajorNames.map((m, i) => (
                  <option key={i} value={m}>
                    {m}
                  </option>
                ))
              ) : (
                <option value="">{isLoading ? "Loading specializations..." : "No specializations available"}</option>
              )}
            </select>
          </div>

  <div className="references-section">
    <h3 className="references-title">References:</h3>
            <ul className="list-disc list-inside space-y-2 text-gray-200">
              {isLoading ? (
                <li className="text-gray-400">Loading references...</li>
              ) : selectedSubjectReferences.length > 0 ? (
                selectedSubjectReferences.map((ref, i) => (
                  <li key={i}>
                    <a
                      href={ref.href}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-blue-400 hover:underline"
                    >
                      {ref.title}
                    </a>
                  </li>
                ))
              ) : (
                <li className="text-White-400">Select a subject node for references.</li>
              )}
            </ul>
          </div>
        </div>

        {/* Graph Viewer */}
<div className="w-full lg:w-2/3 rounded-xl flex flex-col">
  <h2 className="visualization-title text-2xl font-semibold text-blue-800 mb-4" >Visualization</h2>
          <div ref={mermaidContainerRef} className="mermaid-container w-full overflow-auto min-h-[500px]  rounded-lg p-4"

 style={{
              background:'linear-gradient(to bottom right, #4f46e5,rgb(66, 66, 168))',
              borderRadius: "15px",
              transform: "scale(1.15)",
              transformOrigin: "center center",
              overflow: "hidden",
              padding: "20px",
              marginLeft:"45px"
            }}
>
          {isLoading ? (
      <div className="loading-container">
        <p className="text-gray-500">Loading specialization roadmaps...</p>
      </div>
    ) : isRendering ? (
      <div className="loading-container">
        <p className="text-gray-500">Rendering visualization...</p>
      </div>
    ) : svg ? (
      <div className="mermaid-content">
        <div
          key={`mermaid-${selectedMajorName}-${selectedRoadmapType}-${svg.length}`}
          className="mermaid"
          dangerouslySetInnerHTML={{ __html: svg }}
        />
      </div>
    ) : (
      <div className="loading-container">
        <p className="text-gray-500">No roadmap to display for the selected specialization.</p>
      </div>
    )}
  </div>
</div>
   </div>
  </div>
  );
};

export default RoadMap;
