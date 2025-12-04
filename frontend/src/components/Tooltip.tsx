/**
 * Tooltip Component
 * 
 * Accessible tooltip component with ARIA support
 */

import { useState, useRef, useEffect } from 'react';
import { createPortal } from 'react-dom';

interface TooltipProps {
    content: string;
    children: React.ReactElement;
    position?: 'top' | 'bottom' | 'left' | 'right';
    delay?: number;
}

export const Tooltip: React.FC<TooltipProps> = ({
    content,
    children,
    position = 'top',
    delay = 200
}) => {
    const [isVisible, setIsVisible] = useState(false);
    const [coords, setCoords] = useState({ x: 0, y: 0 });
    const triggerRef = useRef<HTMLElement>(null);
    const timeoutRef = useRef<NodeJS.Timeout>();
    const tooltipId = useRef(`tooltip-${Math.random().toString(36).substr(2, 9)}`);

    const showTooltip = () => {
        timeoutRef.current = setTimeout(() => {
            if (triggerRef.current) {
                const rect = triggerRef.current.getBoundingClientRect();
                const scrollX = window.scrollX;
                const scrollY = window.scrollY;

                let x = 0;
                let y = 0;

                switch (position) {
                    case 'top':
                        x = rect.left + rect.width / 2 + scrollX;
                        y = rect.top + scrollY - 8;
                        break;
                    case 'bottom':
                        x = rect.left + rect.width / 2 + scrollX;
                        y = rect.bottom + scrollY + 8;
                        break;
                    case 'left':
                        x = rect.left + scrollX - 8;
                        y = rect.top + rect.height / 2 + scrollY;
                        break;
                    case 'right':
                        x = rect.right + scrollX + 8;
                        y = rect.top + rect.height / 2 + scrollY;
                        break;
                }

                setCoords({ x, y });
                setIsVisible(true);
            }
        }, delay);
    };

    const hideTooltip = () => {
        if (timeoutRef.current) {
            clearTimeout(timeoutRef.current);
        }
        setIsVisible(false);
    };

    useEffect(() => {
        return () => {
            if (timeoutRef.current) {
                clearTimeout(timeoutRef.current);
            }
        };
    }, []);

    const child = React.cloneElement(children, {
        ref: triggerRef,
        onMouseEnter: showTooltip,
        onMouseLeave: hideTooltip,
        onFocus: showTooltip,
        onBlur: hideTooltip,
        'aria-describedby': tooltipId.current,
    });

    const tooltipContent = isVisible && createPortal(
        <div
            id={tooltipId.current}
            role="tooltip"
            className={`
        absolute z-50 px-3 py-2 text-sm text-white bg-gray-900 rounded-md shadow-lg
        pointer-events-none transition-opacity duration-200
        ${isVisible ? 'opacity-100' : 'opacity-0'}
      `}
            style={{
                left: `${coords.x}px`,
                top: `${coords.y}px`,
                transform: position === 'top' || position === 'bottom'
                    ? 'translateX(-50%)'
                    : position === 'left'
                        ? 'translateX(-100%)'
                        : 'translateY(-50%)',
            }}
        >
            {content}
            <div
                className={`
          absolute w-2 h-2 bg-gray-900 transform rotate-45
          ${position === 'top' ? 'bottom-[-4px] left-1/2 -translate-x-1/2' : ''}
          ${position === 'bottom' ? 'top-[-4px] left-1/2 -translate-x-1/2' : ''}
          ${position === 'left' ? 'right-[-4px] top-1/2 -translate-y-1/2' : ''}
          ${position === 'right' ? 'left-[-4px] top-1/2 -translate-y-1/2' : ''}
        `}
            />
        </div>,
        document.body
    );

    return (
        <>
            {child}
            {tooltipContent}
        </>
    );
};
