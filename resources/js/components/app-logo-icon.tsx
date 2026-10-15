import { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M10 24c-3 0-5.5-2.3-5.5-5.2 0-2.4 1.7-4.5 4-5.1C9 10.4 12.2 8 16.2 8c3.3 0 6.1 1.7 7.4 4.2 1-.4 2.1-.6 3.2-.6 4 0 7.2 3 7.2 6.8 0 .4 0 .8-.1 1.2 2.2.7 3.8 2.7 3.8 5.1C37.7 28 35 31 31.4 31H10.8C7.6 31 5 28.6 5 25.7 5 24.9 5.2 24.2 5.6 23.6"
                stroke="currentColor"
                strokeWidth="2.4"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
            <rect x="13" y="18" width="14" height="12" rx="1.5" stroke="currentColor" strokeWidth="2" />
            <path d="M16 22h8M16 25h6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        </svg>
    );
}
