import { useState, useEffect } from 'react';
import { Check, ChevronsUpDown, X } from "lucide-react";
import { cn } from "@/lib/utils";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem } from "@/components/ui/command";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";

const CODFunnelBoosterGeoConfig = ({ 
    countries,
    selectedCountry,
    selectedState,
    onCountryChange,
    onStateChange,
    sellOption,
    onSellOptionChange,
    selectedCountries,
    onSelectedCountriesChange,
    error 
}) => {
    const [states, setStates] = useState([]);
    const [open, setOpen] = useState(false);

    useEffect(() => {
        // Update states when country changes
        if (selectedCountry && countries[selectedCountry]) {
            setStates(Object.entries(countries[selectedCountry].states || {}).map(([code, name]) => ({
                code,
                name
            })));
        }
    }, [selectedCountry, countries]);

    const handleCountrySelect = (countryCode) => {
        const updatedCountries = selectedCountries.includes(countryCode)
            ? selectedCountries.filter(c => c !== countryCode)
            : [...selectedCountries, countryCode];
        
        onSelectedCountriesChange(updatedCountries);
    };

    const renderSelectedCountries = () => {
        if (!countries) return null; // Add null check for countries

        if (sellOption === 'all') {
            return (
                <Badge variant="secondary" className="mr-2">
                    All Countries
                </Badge>
            );
        }

        if (sellOption === 'specific' || sellOption === 'all_except') {
            return selectedCountries?.map(countryCode => (
                <Badge key={countryCode} variant="secondary" className="mr-2">
                    {countries[countryCode]?.name || countryCode}
                    <button 
                        className="ml-1" 
                        onClick={() => handleCountrySelect(countryCode)}
                    >
                        <X className="h-3 w-3" />
                    </button>
                </Badge>
            )) || null;
        }
        
        return null;
    };

    return (
        <div className="space-y-6 bg-card/50 dark:bg-card/30 p-6 rounded-xl border border-border/50 dark:border-border/30">
            <div className="space-y-4">
                <Label className="text-lg font-medium text-foreground">
                    Sales Location Configuration
                </Label>
                <RadioGroup
                    value={sellOption}
                    onValueChange={onSellOptionChange}
                    className="space-y-2"
                >
                    <div className="flex items-center space-x-2">
                        <RadioGroupItem value="all" id="all" />
                        <Label htmlFor="all">Sell to all countries</Label>
                    </div>
                    <div className="flex items-center space-x-2">
                        <RadioGroupItem value="specific" id="specific" />
                        <Label htmlFor="specific">Sell to specific countries</Label>
                    </div>
                    <div className="flex items-center space-x-2">
                        <RadioGroupItem value="all_except" id="all_except" />
                        <Label htmlFor="all_except">Sell to all countries except</Label>
                    </div>
                </RadioGroup>
            </div>

            {(sellOption === 'specific' || sellOption === 'all_except') && (
                <div className="space-y-4">
                    <Label>{sellOption === 'specific' ? 'Select countries' : 'Exclude countries'}</Label>
                    <div className="space-y-2">
                        <div className="flex flex-wrap gap-2 mb-2">
                            {renderSelectedCountries()}
                        </div>
                        <Popover className="bg-slate-400" open={open} onOpenChange={setOpen}>
                            <PopoverTrigger asChild>
                                <Button variant="outline" className="w-full justify-between">
                                    Select countries
                                    <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-600 h-300 p-0 ">
                                <Command>
                                    <CommandInput placeholder="Search country..." />
                                    <CommandEmpty>No country found.</CommandEmpty>
                                    <CommandGroup>
                                        {Object.entries(countries).map(([code, country]) => (
                                            <CommandItem 
                                                key={code}
                                                onSelect={() => handleCountrySelect(code)}
                                            >
                                                <Check
                                                    className={cn(
                                                        "mr-2 h-4 w-4",
                                                        selectedCountries.includes(code) ? "opacity-100" : "opacity-0"
                                                    )}
                                                />
                                                {country.name}
                                            </CommandItem>
                                        ))}
                                    </CommandGroup>
                                </Command>
                            </PopoverContent>
                        </Popover>
                    </div>
                </div>
            )}

            {error && (
                <Alert variant="destructive">
                    <AlertDescription>{error}</AlertDescription>
                </Alert>
            )}
        </div>
    );
};

export default CODFunnelBoosterGeoConfig;