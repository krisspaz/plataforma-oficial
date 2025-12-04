/**
 * Keyboard Shortcuts Hook
 * 
 * Provides keyboard shortcuts for power users
 */

import { useEffect, useCallback } from 'react';

interface ShortcutConfig {
    key: string;
    ctrl?: boolean;
    shift?: boolean;
    alt?: boolean;
    meta?: boolean;
    action: () => void;
    description: string;
}

export const useKeyboardShortcuts = (shortcuts: ShortcutConfig[]) => {
    const handleKeyDown = useCallback((event: KeyboardEvent) => {
        const matchingShortcut = shortcuts.find(shortcut => {
            const keyMatches = event.key.toLowerCase() === shortcut.key.toLowerCase();
            const ctrlMatches = shortcut.ctrl ? event.ctrlKey || event.metaKey : !event.ctrlKey && !event.metaKey;
            const shiftMatches = shortcut.shift ? event.shiftKey : !event.shiftKey;
            const altMatches = shortcut.alt ? event.altKey : !event.altKey;

            return keyMatches && ctrlMatches && shiftMatches && altMatches;
        });

        if (matchingShortcut) {
            event.preventDefault();
            matchingShortcut.action();
        }
    }, [shortcuts]);

    useEffect(() => {
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [handleKeyDown]);
};

/**
 * Global keyboard shortcuts
 */
export const globalShortcuts: ShortcutConfig[] = [
    {
        key: 'k',
        ctrl: true,
        description: 'Abrir búsqueda',
        action: () => {
            // Trigger search modal
            const searchInput = document.querySelector('[data-search-input]') as HTMLInputElement;
            searchInput?.focus();
        }
    },
    {
        key: 'n',
        ctrl: true,
        description: 'Crear nuevo',
        action: () => {
            // Trigger new item modal
            const newButton = document.querySelector('[data-new-button]') as HTMLButtonElement;
            newButton?.click();
        }
    },
    {
        key: '/',
        description: 'Enfocar búsqueda',
        action: () => {
            const searchInput = document.querySelector('[data-search-input]') as HTMLInputElement;
            searchInput?.focus();
        }
    },
    {
        key: 'Escape',
        description: 'Cerrar modal',
        action: () => {
            const closeButton = document.querySelector('[data-modal-close]') as HTMLButtonElement;
            closeButton?.click();
        }
    },
    {
        key: '?',
        shift: true,
        description: 'Mostrar atajos de teclado',
        action: () => {
            // Show shortcuts modal
            console.log('Keyboard shortcuts:', globalShortcuts);
        }
    }
];
