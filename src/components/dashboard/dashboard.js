import { HashRouter as Router, Route, Routes, NavLink } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faHome, faCalendar, faChartLine, faCogs } from '@fortawesome/free-solid-svg-icons';
import Main from './dash-elements/Main';
import Schedule from './dash-elements/Schedule';
import Insights from './dash-elements/Insights';
import ManageAPIs from './dash-elements/ManageIPIs';
import FullscreenButton from '@/components/ui/FullscreenButton';

const Dashboard = () => {
    return (
        <Router>
            <div className="relative post-cfb-container">
                <nav className="post-cfb-nav shadow- hover:shadow-2xl shadow-orange-400 ">
                    <div className="nav-links-wrapper ">
                        <NavLink to="/" 
                                className={({ isActive }) => 
                                    isActive ? 'nav-link active' : 'nav-link'
                                }>
                            <FontAwesomeIcon icon={faHome} className="nav-icon" />
                            Overview
                        </NavLink>
                        <NavLink to="/schedule" 
                                className={({ isActive }) => 
                                    isActive ? 'nav-link active' : 'nav-link'
                                }>
                            <FontAwesomeIcon icon={faCalendar} className="nav-icon" />
                            Schedule
                        </NavLink>
                        <NavLink to="/insights" 
                                className={({ isActive }) => 
                                    isActive ? 'nav-link active' : 'nav-link'
                                }>
                            <FontAwesomeIcon icon={faChartLine} className="nav-icon" />
                            Insights
                        </NavLink>
                        <NavLink to="/manage-apis" 
                                className={({ isActive }) => 
                                    isActive ? 'nav-link active' : 'nav-link'
                                }>
                            <FontAwesomeIcon icon={faCogs} className="nav-icon" />
                            Manage APIs
                        </NavLink>
                        <FullscreenButton />
                    </div>
                    
                </nav>
                <div className="post-cfb-content bg-gradient-to-b from-gray-900 via-gray-800 to-gray-900">
                    <Routes>
                        <Route path="/" element={<Main />} />
                        <Route path="/schedule" element={<Schedule />} />
                        <Route path="/insights" element={<Insights />} />
                        <Route path="/manage-apis" element={<ManageAPIs />} />
                    </Routes>
                </div>
                
            </div>
        </Router>
    );
};

export default Dashboard;