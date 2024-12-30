import { useState, useEffect } from 'react';
import { Check, ChevronsUpDown, X } from "lucide-react";
import { Loader2 } from "lucide-react";
import { Alert, AlertDescription } from "@/components/ui/alert";

import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";

import {
    Command,
    CommandInput,
    CommandEmpty,
    CommandGroup,
    CommandItem,
} from "@/components/ui/command";

import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/ui/popover";

import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

const sellOptions = [
    { value: 'all', label: 'Sell to all countries' },
    { value: 'except', label: 'Sell to all countries except' },
    { value: 'specific', label: 'Sell to specific countries' }
];

const MultiCountrySelect = ({ 
    countries = {},              // Provide default empty object
    selectedCountries = [],      // Ensure it's always an array
    sellOption = 'all',    
    onSelectionChange,     
    onSellOptionChange     
}) => {
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [selected, setSelected] = useState([]);
    const [currentSellOption, setCurrentSellOption] = useState(sellOption);

    // Convert countries object to array format
    const countryList = Object.entries(countries).map(([value, label]) => ({
        value,
        label
    }));

    // Sync with parent data
    useEffect(() => {
        if (Array.isArray(selectedCountries) && countries) {
            const selectedItems = selectedCountries
                .filter(code => countries[code])
                .map(code => ({
                    value: code,
                    label: countries[code]
                }));
            setSelected(selectedItems);
        }
    }, [selectedCountries, countries]);

    useEffect(() => {
        setCurrentSellOption(sellOption);
    }, [sellOption]);

    const handleSelect = (countryValue) => {
        const selectedCountry = countryList.find(c => c.value === countryValue);
        if (selectedCountry) {
            const newSelected = selected.find(item => item.value === countryValue)
                ? selected.filter(item => item.value !== countryValue)
                : [...selected, selectedCountry];
            
            setSelected(newSelected);
            onSelectionChange?.(newSelected.map(country => country.value));
        }
    };

    const handleSellOptionChange = (value) => {
        setCurrentSellOption(value);
        setSelected([]);
        onSellOptionChange?.(value);
    };

    const handleRemove = (countryToRemove) => {
        const newSelected = selected.filter(country => country.value !== countryToRemove.value);
        setSelected(newSelected);
        onSelectionChange?.(newSelected.map(country => country.value));
    };

    return (
        <div className="flex flex-col space-y-4 w-full max-w-sm">
            <Select value={currentSellOption} onValueChange={handleSellOptionChange}>
                <SelectTrigger>
                    <SelectValue placeholder="Select selling option" />
                </SelectTrigger>
                <SelectContent>
                    {sellOptions.map(option => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>

            {currentSellOption !== 'all' && (
                <div className="flex flex-col gap-2">
                    <Popover open={open} onOpenChange={setOpen}>
                        <PopoverTrigger asChild>
                            <Button
                                variant="outline"
                                role="combobox"
                                aria-expanded={open}
                                className="justify-between"
                                disabled={loading}
                            >
                                {loading ? (
                                    <Loader2 className="h-4 w-4 animate-spin" />
                                ) : (
                                    <>
                                        {selected.length 
                                            ? `${selected.length} countries selected`
                                            : `Select ${currentSellOption === 'except' ? 'excluded' : ''} countries`}
                                        <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </>
                                )}
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent className="p-0" align="start">
                            <Command>
                                <CommandInput placeholder="Search countries..." />
                                <CommandEmpty>No country found</CommandEmpty>
                                <CommandGroup className="max-h-64 overflow-y-auto">
                                    {countryList.map((country) => (
                                        <CommandItem
                                            key={country.value}
                                            onSelect={() => handleSelect(country.value)}
                                            className="cursor-pointer"
                                        >
                                            <Check
                                                className={`mr-2 h-4 w-4 ${
                                                    selected.find(item => item.value === country.value)
                                                        ? "opacity-100"
                                                        : "opacity-0"
                                                }`}
                                            />
                                            {country.label}
                                        </CommandItem>
                                    ))}
                                </CommandGroup>
                            </Command>
                        </PopoverContent>
                    </Popover>

                    <div className="flex flex-wrap gap-2">
                        {selected.map((country) => (
                            <Badge
                                key={country.value}
                                variant="secondary"
                                className="flex items-center gap-1 px-3 py-1"
                            >
                                {country.label}
                                <X
                                    className="h-3.5 w-3.5 cursor-pointer hover:text-destructive transition-colors"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        handleRemove(country);
                                    }}
                                />
                            </Badge>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

export default MultiCountrySelect;