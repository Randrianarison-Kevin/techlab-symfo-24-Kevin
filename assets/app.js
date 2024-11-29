import { registerReactControllerComponents } from '@symfony/ux-react';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import './bootstrap'
import { createRoot } from 'react-dom/client';
import Hello from './react/controllers/Hello';
console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
// const context = require.context('./react/controllers', true, /\.(j|t)sx?$/);
registerReactControllerComponents(require.context('./react/controllers', true, /\.(j|t)sx?$/));
// console.log(context.keys());
// registerReactControllerComponents(context);
// const rootElement = document.getElementById('root'); 
// if (rootElement) 
// { 
//     const root = createRoot(rootElement); 
//     root.render(<Hello fullName="Fabien" />); 
//     console.log('Composant Hello rendu dans #root'); 
// } else { 
//     console.error('Élément root introuvable'); 
// }