import { Component, EventListeners, DelegateEvent } from '@mangoweb/scripts-base'

interface StickySidebarProps {
	sidebarEl: string
	mainEl: string
}

export class StickySidebar extends Component<StickySidebarProps> {
	static componentName = 'StickySidebar'

	sidebar: HTMLElement
	main: HTMLElement

	constructor(el: HTMLElement, props: StickySidebarProps) {
		super(el, props)

		this.sidebar = this.el.querySelector(this.props.sidebarEl) as HTMLElement
		this.main = this.el.querySelector(this.props.mainEl) as HTMLElement
		
		this.stickySidebar()
		document.addEventListener('scroll', () => {
			this.stickySidebar()
		})
		
	}

	stickySidebar(){
		const mainHeight = this.main.getBoundingClientRect().height
		this.el.style.height = `${mainHeight}px`
	}
}
