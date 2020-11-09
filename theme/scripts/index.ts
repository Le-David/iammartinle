import { initializeComponents } from '@mangoweb/scripts-base'

import './plugins'

import { StickySidebar } from './components/StickySidebar'
import { Example } from './components/Example'
import { ShapesFallback } from '@mangoweb/shapes-fallback'

// Sort the components alphabetically…
initializeComponents([Example, ShapesFallback, StickySidebar], 'initComponents')
