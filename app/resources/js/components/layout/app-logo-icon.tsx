import type { SVGProps } from 'react';

/**
 * SocialApp change: Y5Media "Y5" mark (from the Y5Media brand logo, without the small
 * "MEDIA" lettering) replaces the Shoutrrr megaphone. Wide mark (about 2.1:1).
 */
export default function AppLogoIcon(props: SVGProps<SVGSVGElement>) {
    return (
        <svg
            viewBox="8 5 157 74"
            fill="currentColor"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
            {...props}
        >
            <path d="M133,76.9h6.4c12.9,0,23.3-10.4,23.3-23.3c0-12.9-10.4-23.3-23.3-23.3H133l-24,0.4v-8.5h53.9V7.7H109L91.3,7.9v36.9l6.7,0.1l40.8,0.2c4.3,0,7.9,3.5,7.9,7.9c0,4.4-3.5,7.9-7.9,7.9H91.1l0,15.9L70.8,53.3l0,23.3L133,76.9z" />
            <path d="M109.6,7.7l-25.4,0L59.6,36.5L37,7.6H10.7l37.7,45.6v23.5c0,0,9.4,0,22.4,0V53.3l15-18.8L109.6,7.7z" />
        </svg>
    );
}
