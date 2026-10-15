import '../../css/app.css';

import { createRoot } from 'react-dom/client';
import App from './App';

const el = document.getElementById('spa');

if (el) {
    createRoot(el).render(<App />);
}
