import { Profiler, useState } from 'react'
import './App.css'
import Register from './Components/Register'
import { Route, Routes } from 'react-router-dom'
import Login from './Components/Login'
import Forget from './Components/Forget'
import ResPass from './Components/ResetPassword/ResPass'
import Home from './Components/Home/Home'
import Verify from './Verify'
import Profile from './Components/profile/Profile'
import StudentGroups from './Components/StudentsGroups/StudentGroups'
import RoadMap from './Components/RoadMap/RoadMap'
import Subjects from './Components/Subjects/Subjects'
import NewSubjects from './Components/NewSubjects/NewSubjects'
import { Check } from 'lucide-react'
import CheckSubjects from './Components/CheckSubjects/CheckSubjects'
import Lecturers from './Components/Lecturers/Lecturers'
import FeedbackForm from './Components/FeedBack/FeedbackForm'
import EntireSubjects from './Components/EntireSubjects/EntireSubjects'
function App() {

return (
  <Routes>
<Route path='/' element ={<Register />} />
<Route path='/login' element ={<Login />} />
<Route path='/Home' element ={<Home/>} />
<Route path='/Profile' element ={<Profile/>} />
<Route path='/Forget' element ={<Forget/>} />
<Route path='/ResPass' element ={<ResPass/>} />
<Route path='/Verify' element ={<Verify />} /> 
<Route path='/Groups' element ={<StudentGroups/>} />
<Route path='/Roadmap' element ={<RoadMap/>} />
<Route path="/subjects/:subjectId/lectures" element={<Lecturers />} />
<Route path="/subjects/:subjectId/summaries" element={<Lecturers />} /> 
<Route path='/NewSubjects' element ={<NewSubjects/>} />
<Route path='/Verify/Check' element ={<CheckSubjects/>} />
<Route path='/Profile/Lecturers' element ={<Lecturers/>} />
<Route path='/FeedBack' element ={<FeedbackForm/>} />
<Route path='/entire-subjects' element ={<EntireSubjects/>} />



 </Routes>
  
)

}

export default App
